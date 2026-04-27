<?php

namespace App\Http\Controllers\FileManager;

use FileManager\Requests\FileShowRequest;
use FileManager\Resources\FileManagerPathResource;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * GET /files/{pathId} — returns details of a single file or directory entry.
 */
readonly class GetFileController
{
    /**
     * @param FileManagerServiceInterface $fileManager file manager service
     */
    public function __construct(private FileManagerServiceInterface $fileManager)
    {
    }

    /**
     * @param FileShowRequest $request validated show request carrying the route path id
     * @return JsonResponse serialized entry with relations
     */
    public function __invoke(FileShowRequest $request): JsonResponse
    {
        $item = $this->fileManager->getItem($request->getDto());
        return response()->json(new FileManagerPathResource($item));
    }
}
