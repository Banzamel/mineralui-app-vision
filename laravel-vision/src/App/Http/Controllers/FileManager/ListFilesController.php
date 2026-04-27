<?php

namespace App\Http\Controllers\FileManager;

use FileManager\Requests\FileListRequest;
use FileManager\Resources\FileManagerPathResource;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * GET /files — returns the contents of a directory for the caller's tenant.
 */
readonly class ListFilesController
{
    /**
     * @param FileManagerServiceInterface $fileManager file manager service
     */
    public function __construct(private FileManagerServiceInterface $fileManager)
    {
    }

    /**
     * @param FileListRequest $request validated list query scoped to the current tenant
     * @return JsonResponse directory contents serialized via FileManagerPathResource
     */
    public function __invoke(FileListRequest $request): JsonResponse
    {
        $items = $this->fileManager->listDirectory($request->getDto());
        return FileManagerPathResource::collection($items)->response();
    }
}
