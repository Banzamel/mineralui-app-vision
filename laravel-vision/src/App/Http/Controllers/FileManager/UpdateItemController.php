<?php

namespace App\Http\Controllers\FileManager;

use FileManager\Requests\UpdateItemRequest;
use FileManager\Resources\FileManagerPathResource;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * PUT /files/{pathId} — renames or moves a file/directory.
 */
readonly class UpdateItemController
{
    /**
     * @param FileManagerServiceInterface $fileManager file manager service
     */
    public function __construct(private FileManagerServiceInterface $fileManager)
    {
    }

    /**
     * @param UpdateItemRequest $request validated update request carrying the route path id
     * @return JsonResponse updated entry with meta
     */
    public function __invoke(UpdateItemRequest $request): JsonResponse
    {
        $item = $this->fileManager->updateItem($request->getDto());
        return response()->json(new FileManagerPathResource($item));
    }
}
