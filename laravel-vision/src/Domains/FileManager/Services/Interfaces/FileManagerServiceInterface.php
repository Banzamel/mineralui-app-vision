<?php

namespace FileManager\Services\Interfaces;

use Administration\Models\User;
use FileManager\Dtos\CreateDirectoryDto;
use FileManager\Dtos\DeleteItemDto;
use FileManager\Dtos\DownloadFileDto;
use FileManager\Dtos\FileListDto;
use FileManager\Dtos\FileShowDto;
use FileManager\Dtos\UpdateItemDto;
use FileManager\Dtos\UploadFileDto;
use FileManager\Models\FileManagerPath;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * File manager service contract — every input enters as a DTO so the service never reads auth() directly.
 * Uploads take a `User $actor` parameter so the file-upload event can carry the uploader.
 */
interface FileManagerServiceInterface
{
    /**
     * Returns the list of files and directories located in the given tenant directory.
     *
     * @param FileListDto $dto tenant scope + parent directory id
     * @return Collection collection of FileManagerPath entries
     */
    public function listDirectory(FileListDto $dto): Collection;

    /**
     * Fetches a single file or directory along with its relations.
     *
     * @param FileShowDto $dto item id wrapper
     * @return FileManagerPath entry with meta + children + links preloaded
     */
    public function getItem(FileShowDto $dto): FileManagerPath;

    /**
     * Creates a new directory on disk and in the database.
     *
     * @param CreateDirectoryDto $dto tenant scope + directory spec
     * @return FileManagerPath created directory entry
     */
    public function createDirectory(CreateDirectoryDto $dto): FileManagerPath;

    /**
     * Uploads a file, persists it on disk and creates the metadata entry.
     *
     * @param UploadFileDto $dto tenant scope + uploaded file + target directory
     * @param User $actor user performing the upload (carried in the file-upload event)
     * @return FileManagerPath created file entry with meta
     */
    public function uploadFile(UploadFileDto $dto, User $actor): FileManagerPath;

    /**
     * Renames/moves a file or directory.
     *
     * @param UpdateItemDto $dto item id + changes
     * @return FileManagerPath updated entry with meta
     */
    public function updateItem(UpdateItemDto $dto): FileManagerPath;

    /**
     * Deletes a file or directory both from disk and from the database.
     *
     * @param DeleteItemDto $dto item id wrapper
     * @return void
     */
    public function deleteItem(DeleteItemDto $dto): void;

    /**
     * Streams a file to the caller as a downloadable response.
     *
     * @param DownloadFileDto $dto item id wrapper
     * @return StreamedResponse streaming response with the file
     */
    public function downloadFile(DownloadFileDto $dto): StreamedResponse;

    /**
     * Finds or creates a directory by name — idempotent helper used by observers running in console.
     * Takes an explicit companyId because observers run outside the HTTP auth context.
     *
     * @param string $name directory name (will be slugified on creation)
     * @param int $companyId tenant id
     * @param int|null $parentId parent directory id or null for the company root
     * @param string|null $storage disk name (defaults to "local")
     * @return FileManagerPath resolved or newly created directory entry
     */
    public function findOrCreateDirectory(string $name, int $companyId, ?int $parentId = null, ?string $storage = null): FileManagerPath;
}
