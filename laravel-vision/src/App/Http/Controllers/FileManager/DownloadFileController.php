<?php

namespace App\Http\Controllers\FileManager;

use FileManager\Requests\DownloadFileRequest;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * GET /files/{pathId}/download — streams a file to the caller as a downloadable response.
 */
readonly class DownloadFileController
{
    /**
     * @param FileManagerServiceInterface $fileManager file manager service
     */
    public function __construct(private FileManagerServiceInterface $fileManager)
    {
    }

    /**
     * @param DownloadFileRequest $request validated download request
     * @return StreamedResponse streaming response with the file content
     */
    public function __invoke(DownloadFileRequest $request): StreamedResponse
    {
        return $this->fileManager->downloadFile($request->getDto());
    }
}
