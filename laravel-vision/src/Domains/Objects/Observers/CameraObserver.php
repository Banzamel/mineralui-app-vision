<?php

namespace Objects\Observers;

use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Objects\Models\Camera;
use Objects\Models\VisionObject;

/**
 * Camera observer — wires the model lifecycle with FileManager directories.
 * On create it materialises the path {companyId}/{rootObject}/.../{parentObject}/{cameraSlug},
 * mirroring the VisionObject tree on disk so albums land at a stable, human-readable location.
 * Deletion is handled by AlbumObserver + RetentionService.
 */
class CameraObserver
{
    /**
     * @param FileManagerServiceInterface $fileManager Service for directory management.
     */
    public function __construct(
        protected FileManagerServiceInterface $fileManager,
    ) {
    }

    /**
     * After creating the camera, materialises {companyId}/{object-tree}/{cameraSlug} on disk
     * and stores the leaf id in file_manager_path_id.
     *
     * @param Camera $camera Created camera.
     * @return void
     */
    public function created(Camera $camera): void
    {
        $parentDirId = $this->ensureObjectTreePath($camera->company_id, $camera->object_id);

        $folder = $this->fileManager->findOrCreateDirectory(
            $camera->slug,
            $camera->company_id,
            $parentDirId,
        );

        if ($folder->id !== $camera->file_manager_path_id) {
            $camera->file_manager_path_id = $folder->id;
            $camera->saveQuietly();
        }
    }

    /**
     * Creates the directory chain that mirrors the VisionObject ancestry (root → … → object).
     * Returns the FileManagerPath id of the deepest directory, which becomes the camera folder's parent.
     *
     * @param int $companyId Tenant scope.
     * @param int $objectId Camera's parent VisionObject id.
     * @return int FileManagerPath id of the leaf directory in the ancestry chain.
     */
    private function ensureObjectTreePath(int $companyId, int $objectId): int
    {
        $object = VisionObject::withoutGlobalScopes()->findOrFail($objectId);
        $chain = [];
        for ($node = $object; $node !== null; $node = $node->parent_id ? VisionObject::withoutGlobalScopes()->find($node->parent_id) : null) {
            array_unshift($chain, $node);
        }

        $parentDirId = null;
        foreach ($chain as $node) {
            $dir = $this->fileManager->findOrCreateDirectory($node->slug, $companyId, $parentDirId);
            $parentDirId = $dir->id;
        }

        return $parentDirId;
    }

    /**
     * After deleting the camera we leave the directory alone — photos stay for retention.
     * The directory and its contents are cleaned up by RetentionService per the configured period.
     *
     * @param Camera $camera Deleted camera.
     * @return void
     */
    public function deleted(Camera $camera): void
    {
        // no-op — RetentionService will handle the directory after the retention period
    }
}
