<?php

namespace App\Http\Controllers\FileManager;

use FileManager\Requests\UploadFileRequest;
use FileManager\Resources\FileManagerPathResource;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * POST /files/upload — uploads a new file into the caller's tenant.
 */
readonly class UploadFileController
{
    /**
     * @param FileManagerServiceInterface $fileManager file manager service
     */
    public function __construct(private FileManagerServiceInterface $fileManager)
    {
    }

    /**
     * @param UploadFileRequest $request validated upload request with the file and target directory
     * @return JsonResponse created file entry with meta (status 201)
     */
    public function __invoke(UploadFileRequest $request): JsonResponse
    {
        $filePath = $this->fileManager->uploadFile($request->getDto(), $request->user());
        return response()->json(new FileManagerPathResource($filePath), 201);
    }
}
