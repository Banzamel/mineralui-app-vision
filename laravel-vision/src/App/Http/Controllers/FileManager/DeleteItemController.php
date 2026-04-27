<?php

namespace App\Http\Controllers\FileManager;

use FileManager\Requests\DeleteItemRequest;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * DELETE /files/{pathId} — deletes a file or directory both on disk and in the database.
 */
readonly class DeleteItemController
{
    /**
     * @param FileManagerServiceInterface $fileManager file manager service
     */
    public function __construct(private FileManagerServiceInterface $fileManager)
    {
    }

    /**
     * @param DeleteItemRequest $request validated delete request
     * @return JsonResponse empty response (status 204)
     */
    public function __invoke(DeleteItemRequest $request): JsonResponse
    {
        $this->fileManager->deleteItem($request->getDto());
        return response()->json(null, 204);
    }
}
