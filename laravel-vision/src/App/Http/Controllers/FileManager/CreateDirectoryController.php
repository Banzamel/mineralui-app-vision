<?php

namespace App\Http\Controllers\FileManager;

use FileManager\Requests\CreateDirectoryRequest;
use FileManager\Resources\FileManagerPathResource;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * POST /files/directory — creates a new directory in the caller's tenant.
 */
readonly class CreateDirectoryController
{
    /**
     * @param FileManagerServiceInterface $fileManager file manager service
     */
    public function __construct(private FileManagerServiceInterface $fileManager)
    {
    }

    /**
     * @param CreateDirectoryRequest $request validated directory creation request
     * @return JsonResponse created directory entry (status 201)
     */
    public function __invoke(CreateDirectoryRequest $request): JsonResponse
    {
        $directory = $this->fileManager->createDirectory($request->getDto());
        return response()->json(new FileManagerPathResource($directory), 201);
    }
}
