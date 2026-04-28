<?php

namespace Objects\Services;

use Albums\Services\Interfaces\AlbumServiceInterface;
use FileManager\Dtos\DeleteItemDto;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Objects\Dtos\CreateCameraDto;
use Objects\Dtos\UpdateCameraDto;
use Objects\Events\CameraCreatedEvent;
use Objects\Events\CameraDeletedEvent;
use Objects\Events\CameraUpdatedEvent;
use Objects\Models\Camera;
use Objects\Repositories\Interfaces\CameraRepositoryInterface;
use Objects\Services\Interfaces\CameraServiceInterface;

/**
 * Cameras business logic service.
 * Encrypts/decrypts the RTSP stream password and emits events.
 */
class CameraService implements CameraServiceInterface
{
    /**
     * @param CameraRepositoryInterface $repository Cameras repository.
     * @param FileManagerServiceInterface $fileManager FileManager (creates/removes the camera folder + mgr_file_paths record).
     * @param AlbumServiceInterface $albums Albums service (cascade delete of albums+photos when removing a camera).
     */
    public function __construct(
        protected CameraRepositoryInterface $repository,
        protected FileManagerServiceInterface $fileManager,
        protected AlbumServiceInterface $albums,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function list(): Collection
    {
        return $this->repository->all();
    }

    /**
     * @inheritDoc
     */
    public function byObject(int $objectId): Collection
    {
        return $this->repository->byObject($objectId);
    }

    /**
     * @inheritDoc
     */
    public function find(int $id): Camera
    {
        return $this->repository->findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function create(CreateCameraDto $dto): Camera
    {
        return DB::transaction(function () use ($dto) {
            $data = [
                'object_id' => $dto->objectId,
                'name' => $dto->name,
                'display_name' => $dto->displayName ?? $dto->name,
                'slug' => $this->uniqueSlug($dto->name),
                'address' => $dto->address,
                'ip' => $dto->ip,
                'stream_url' => $dto->streamUrl,
                'stream_login' => $dto->streamLogin,
                'stream_password_encrypted' => $dto->streamPassword !== null
                    ? Crypt::encryptString($dto->streamPassword)
                    : null,
                'main_photo_path' => $dto->mainPhotoPath,
                'is_online' => false,
                'motion_preview_enabled' => $dto->motionPreviewEnabled,
            ];

            $camera = $this->repository->create($data);

            $this->bindFileManagerFolder($camera);

            event(new CameraCreatedEvent($camera));
            return $camera;
        });
    }

    /**
     * Creates the camera folder on disk (and its mgr_file_paths record) and stores the
     * `file_manager_path_id` on the Camera entity. Without this, AlbumSyncService::syncCamera()
     * skips the camera on every run (early return on missing path_id).
     *
     * Disk layout: storage/app/private/<companyId>/<object-slug>/<camera-slug>/
     * If the object does not have its own folder yet, creates one under the company root and
     * links it (lazy bootstrap for projects created before this fix).
     *
     * @param Camera $camera Freshly created camera (already persisted).
     */
    protected function bindFileManagerFolder(Camera $camera): void
    {
        $object = $camera->object;
        if ($object === null) {
            return;
        }

        // findOrCreateDirectory is idempotent — looks up by (companyId, parentId, name) first
        // and only creates when missing. We don't need to cache the object's file_manager_path_id
        // on `vision_objects` (the table doesn't even have that column) — the lookup costs
        // one indexed SELECT per camera-create, which is fine for an admin-rarely-used flow.
        $objectFolder = $this->fileManager->findOrCreateDirectory(
            $object->slug,
            (int) $camera->company_id,
            null,
            'local',
        );

        $cameraFolder = $this->fileManager->findOrCreateDirectory(
            $camera->slug,
            (int) $camera->company_id,
            $objectFolder->id,
            'local',
        );

        $this->repository->update($camera, ['file_manager_path_id' => $cameraFolder->id]);
        $camera->file_manager_path_id = $cameraFolder->id;
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, UpdateCameraDto $dto): Camera
    {
        return DB::transaction(function () use ($id, $dto) {
            $camera = $this->repository->findOrFail($id);

            $data = array_filter([
                'object_id' => $dto->objectId,
                'name' => $dto->name,
                'display_name' => $dto->displayName,
                'address' => $dto->address,
                'ip' => $dto->ip,
                'stream_url' => $dto->streamUrl,
                'stream_login' => $dto->streamLogin,
                'main_photo_path' => $dto->mainPhotoPath,
                'motion_preview_enabled' => $dto->motionPreviewEnabled,
            ], fn ($v) => $v !== null);

            if ($dto->streamPassword !== null) {
                $data['stream_password_encrypted'] = Crypt::encryptString($dto->streamPassword);
            }

            $updated = $this->repository->update($camera, $data);
            event(new CameraUpdatedEvent($updated));
            return $updated;
        });
    }

    /**
     * @inheritDoc
     */
    public function updateMainPhoto(int $id, UploadedFile $file): Camera
    {
        $camera = $this->repository->findOrFail($id);

        if ($camera->main_photo_path) {
            Storage::disk('public')->delete($camera->main_photo_path);
        }

        $path = $file->storePublicly("camera-photos/{$camera->company_id}/{$camera->id}", 'public');
        $updated = $this->repository->update($camera, ['main_photo_path' => $path]);
        event(new CameraUpdatedEvent($updated));

        return $updated;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $camera = $this->repository->findOrFail($id);

            // Cascade: delete each album through AlbumService — that in turn removes all
            // photos (Photo rows) and fires AlbumDeletedEvent (frontend / WS receive the update).
            // Photo files on disk live in daily folders under the camera folder —
            // they disappear together with the camera folder in the next step.
            foreach ($camera->albums as $album) {
                $this->albums->delete($album->id);
            }

            $this->removeCameraFolder($camera);

            $this->repository->delete($camera);
            event(new CameraDeletedEvent($camera));
        });
    }

    /**
     * Removes the camera folder from disk. Tries two strategies in order:
     *  1. Through FileManagerService (clean path — respects mgr_file_paths record).
     *  2. Convention-based fallback `<companyId>/<object-slug>/<camera-slug>/` — covers
     *     legacy cameras created before bindFileManagerFolder existed and cases where
     *     the folder was created manually outside the FileManager flow.
     *
     * Failures are tolerated — disk delete returning false (e.g. folder already gone)
     * does not abort the transaction.
     *
     * @param Camera $camera Camera being deleted.
     */
    protected function removeCameraFolder(Camera $camera): void
    {
        if ($camera->file_manager_path_id) {
            try {
                $this->fileManager->deleteItem(new DeleteItemDto((int) $camera->file_manager_path_id));
                return;
            } catch (\Throwable $e) {
                // mgr_file_paths record may have been removed manually — fall through to convention.
            }
        }

        $object = $camera->object;
        if ($object === null) {
            return;
        }

        $conventional = sprintf('%d/%s/%s', (int) $camera->company_id, $object->slug, $camera->slug);
        Storage::disk('local')->deleteDirectory($conventional);
    }

    /**
     * @inheritDoc
     */
    public function decryptPassword(Camera $camera): ?string
    {
        if (empty($camera->stream_password_encrypted)) {
            return null;
        }
        return Crypt::decryptString($camera->stream_password_encrypted);
    }

    /**
     * Generates a unique slug within the company (via the repository's slugExists check).
     *
     * @param string $name camera name to base the slug on
     * @return string slug guaranteed to be free
     */
    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'camera';
        $slug = $base;
        $i = 1;
        while ($this->repository->slugExists($slug)) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
