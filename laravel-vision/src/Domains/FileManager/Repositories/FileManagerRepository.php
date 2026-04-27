<?php

namespace FileManager\Repositories;

use FileManager\Enums\EntityTypeEnum;
use FileManager\Repositories\Interfaces\FileManagerRepositoryInterface;
use FileManager\Models\FileManagerPath;
use FileManager\Models\FileManagerMeta;
use Illuminate\Database\Eloquent\Collection;

/**
 * File manager repository - handles writes and reads of file paths and metadata from the database.
 */
class FileManagerRepository implements FileManagerRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function listDirectory(?int $parentId = null): Collection
    {
        return FileManagerPath::where('parent_id', $parentId)
            ->with('meta')
            ->orderByRaw("FIELD(type, ?, ?) ASC", [EntityTypeEnum::dir->value, EntityTypeEnum::file->value])
            ->orderBy('name')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function findOrFail(int $pathId, array $with = []): FileManagerPath
    {
        return FileManagerPath::query()
            ->when(!empty($with), fn($q) => $q->with($with))
            ->findOrFail($pathId);
    }

    /**
     * @inheritDoc
     */
    public function createPath(array $data): FileManagerPath
    {
        return FileManagerPath::create($data);
    }

    /**
     * @inheritDoc
     */
    public function createMeta(array $data): FileManagerMeta
    {
        return FileManagerMeta::create($data);
    }

    /**
     * @inheritDoc
     */
    public function updatePath(FileManagerPath $path, array $data): FileManagerPath
    {
        $path->update($data);
        return $path->fresh();
    }

    /**
     * @inheritDoc
     */
    public function delete(FileManagerPath $path): void
    {
        $path->delete();
    }

    /**
     * @inheritDoc
     */
    public function findDirectory(int $companyId, ?int $parentId, string $name): ?FileManagerPath
    {
        return FileManagerPath::query()
            ->withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('parent_id', $parentId)
            ->where('type', EntityTypeEnum::dir->value)
            ->where('name', $name)
            ->first();
    }
}
