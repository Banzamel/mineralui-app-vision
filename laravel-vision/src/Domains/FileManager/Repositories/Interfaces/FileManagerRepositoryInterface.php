<?php

namespace FileManager\Repositories\Interfaces;

use FileManager\Models\FileManagerPath;
use FileManager\Models\FileManagerMeta;
use Illuminate\Database\Eloquent\Collection;

/**
 * File manager repository contract - defines database operations for file paths and metadata.
 */
interface FileManagerRepositoryInterface
{
    /**
     * Fetches contents of the given directory (or the company root when parent_id is null).
     *
     * @param int|null $parentId Parent directory ID or null for the root.
     * @return Collection Collection of file and directory entries.
     */
    public function listDirectory(?int $parentId = null): Collection;

    /**
     * Finds a single file entry by ID or throws an exception if it does not exist.
     *
     * @param int $pathId File entry ID.
     * @param array $with List of relations to load alongside the entry.
     * @return FileManagerPath Found file entry.
     */
    public function findOrFail(int $pathId, array $with = []): FileManagerPath;

    /**
     * Creates a new file or directory entry in the database.
     *
     * @param array $data New entry data.
     * @return FileManagerPath Newly created entry.
     */
    public function createPath(array $data): FileManagerPath;

    /**
     * Creates a new file metadata entry in the database.
     *
     * @param array $data Metadata fields.
     * @return FileManagerMeta Newly created metadata.
     */
    public function createMeta(array $data): FileManagerMeta;

    /**
     * Updates a file entry with the given data and returns the refreshed version.
     *
     * @param FileManagerPath $path Entry to update.
     * @param array $data Fields to change.
     * @return FileManagerPath Updated entry.
     */
    public function updatePath(FileManagerPath $path, array $data): FileManagerPath;

    /**
     * Deletes a file or directory entry from the database.
     *
     * @param FileManagerPath $path Entry to delete.
     * @return void
     */
    public function delete(FileManagerPath $path): void;

    /**
     * Finds a directory by name inside the given parent for a specific company.
     * Bypasses global scopes so that console-run observers can resolve directories without auth context.
     *
     * @param int $companyId tenant id
     * @param int|null $parentId parent directory id or null for the company root
     * @param string $name directory name to match
     * @return FileManagerPath|null directory entry when found, otherwise null
     */
    public function findDirectory(int $companyId, ?int $parentId, string $name): ?FileManagerPath;
}
