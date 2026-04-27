<?php

namespace FileManager\Services;

use Administration\Models\User;
use FileManager\Dtos\CreateDirectoryDto;
use FileManager\Dtos\DeleteItemDto;
use FileManager\Dtos\DownloadFileDto;
use FileManager\Dtos\FileListDto;
use FileManager\Dtos\FileShowDto;
use FileManager\Dtos\UpdateItemDto;
use FileManager\Dtos\UploadFileDto;
use FileManager\Enums\EntityTypeEnum;
use FileManager\Enums\StoragesEnum;
use FileManager\Events\FileUploadEvent;
use FileManager\Models\FileManagerPath;
use FileManager\Repositories\Interfaces\FileManagerRepositoryInterface;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * File manager service — composes database entries (through the repository) with disk operations.
 * Input always flows in as a DTO; auth context enters via the DTO's companyId or the explicit actor param.
 */
readonly class FileManagerService implements FileManagerServiceInterface
{
    /**
     * @param FileManagerRepositoryInterface $fileManagerRepository file manager repository
     */
    public function __construct(
        private FileManagerRepositoryInterface $fileManagerRepository,
    ) {}

    /**
     * @inheritDoc
     */
    public function listDirectory(FileListDto $dto): Collection
    {
        return $this->fileManagerRepository->listDirectory($dto->getParentId());
    }

    /**
     * @inheritDoc
     */
    public function getItem(FileShowDto $dto): FileManagerPath
    {
        return $this->fileManagerRepository->findOrFail($dto->getPathId(), ['meta', 'children', 'links']);
    }

    /**
     * @inheritDoc
     */
    public function createDirectory(CreateDirectoryDto $dto): FileManagerPath
    {
        $storageDisk = $this->resolveStorage($dto->getStorage());
        $parentPath = $this->resolveParentPath($dto->getCompanyId(), $dto->getParentId());
        $directoryPath = $parentPath . '/' . Str::slug($dto->getName());

        Storage::disk($storageDisk->value)->makeDirectory($directoryPath);

        return $this->fileManagerRepository->createPath([
            'company_id' => $dto->getCompanyId(),
            'hash' => Str::random(31),
            'parent_id' => $dto->getParentId(),
            'type' => EntityTypeEnum::dir->value,
            'storage' => $storageDisk->value,
            'name' => $dto->getName(),
            'path' => $directoryPath,
            'size' => 0,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function uploadFile(UploadFileDto $dto, User $actor): FileManagerPath
    {
        $file = $dto->getFile();
        $storageDisk = $this->resolveStorage($dto->getStorage());
        $parentPath = $this->resolveParentPath($dto->getCompanyId(), $dto->getParentId());

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $hash = Str::random(31);
        $storedName = $hash . '.' . $extension;

        $storedPath = $file->storeAs($parentPath, $storedName, $storageDisk->value);

        $filePath = $this->fileManagerRepository->createPath([
            'company_id' => $dto->getCompanyId(),
            'hash' => $hash,
            'parent_id' => $dto->getParentId(),
            'type' => EntityTypeEnum::file->value,
            'storage' => $storageDisk->value,
            'name' => $originalName,
            'path' => $storedPath,
            'size' => $file->getSize(),
        ]);

        $this->fileManagerRepository->createMeta([
            'path_id' => $filePath->id,
            'hash' => $hash,
            'mime_type' => $file->getMimeType(),
            'extension' => $extension,
            'checksum' => md5_file($file->getRealPath()),
        ]);

        event(new FileUploadEvent($actor, $filePath));

        return $filePath->load('meta');
    }

    /**
     * @inheritDoc
     */
    public function updateItem(UpdateItemDto $dto): FileManagerPath
    {
        $item = $this->fileManagerRepository->findOrFail($dto->getPathId());

        $updateData = [];
        if ($dto->getName() !== null && $dto->getName() !== $item->name) {
            $updateData['name'] = $dto->getName();
        }
        if ($dto->getParentId() !== null) {
            $updateData['parent_id'] = $dto->getParentId();
        }

        if (!empty($updateData)) {
            $item = $this->fileManagerRepository->updatePath($item, $updateData);
        }

        return $item->load('meta');
    }

    /**
     * @inheritDoc
     */
    public function deleteItem(DeleteItemDto $dto): void
    {
        $item = $this->fileManagerRepository->findOrFail($dto->getPathId());

        if ($item->isFile()) {
            Storage::disk($item->storage->value)->delete($item->path);
        } elseif ($item->isDirectory()) {
            Storage::disk($item->storage->value)->deleteDirectory($item->path);
        }

        $this->fileManagerRepository->delete($item);
    }

    /**
     * @inheritDoc
     */
    public function downloadFile(DownloadFileDto $dto): StreamedResponse
    {
        $item = $this->fileManagerRepository->findOrFail($dto->getPathId(), ['meta']);

        if ($item->isDirectory()) {
            throw new NotFoundHttpException('Cannot download a directory.');
        }

        $disk = Storage::disk($item->storage->value);

        if (!$disk->exists($item->path)) {
            throw new NotFoundHttpException('File not found on storage.');
        }

        return $disk->download($item->path, $item->name);
    }

    /**
     * @inheritDoc
     */
    public function findOrCreateDirectory(string $name, int $companyId, ?int $parentId = null, ?string $storage = null): FileManagerPath
    {
        $existing = $this->fileManagerRepository->findDirectory($companyId, $parentId, $name);
        if ($existing) {
            return $existing;
        }

        $storageDisk = $this->resolveStorage($storage);
        $parentPath = $parentId
            ? $this->fileManagerRepository->findOrFail($parentId)->path
            : $this->companyRoot($companyId);
        $directoryPath = $parentPath . '/' . Str::slug($name);

        Storage::disk($storageDisk->value)->makeDirectory($directoryPath);

        return $this->fileManagerRepository->createPath([
            'company_id' => $companyId,
            'hash' => Str::random(31),
            'parent_id' => $parentId,
            'type' => EntityTypeEnum::dir->value,
            'storage' => $storageDisk->value,
            'name' => $name,
            'path' => $directoryPath,
            'size' => 0,
        ]);
    }

    /**
     * Resolves the on-disk parent directory path for a given tenant.
     *
     * @param int $companyId tenant id
     * @param int|null $parentId parent directory id or null for the tenant root
     * @return string absolute path within the selected disk
     */
    private function resolveParentPath(int $companyId, ?int $parentId): string
    {
        if ($parentId) {
            return $this->fileManagerRepository->findOrFail($parentId)->path;
        }
        return $this->companyRoot($companyId);
    }

    /**
     * Returns the on-disk root path for a tenant.
     * The path is just the numeric company id — no "companies/" prefix — so that the storage
     * tree reads as {companyId}/{object-tree}/{camera}/{day}/photo.jpg.
     *
     * @param int $companyId tenant id
     * @return string company root path
     */
    private function companyRoot(int $companyId): string
    {
        return (string) $companyId;
    }

    /**
     * Resolves the disk enum with a safe fallback to the local disk.
     *
     * @param string|null $storage disk name or null
     * @return StoragesEnum resolved disk enum
     */
    private function resolveStorage(?string $storage): StoragesEnum
    {
        return StoragesEnum::tryFrom($storage ?? 'local') ?? StoragesEnum::local;
    }
}
