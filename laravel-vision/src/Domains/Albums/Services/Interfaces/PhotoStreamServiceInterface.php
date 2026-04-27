<?php

namespace Albums\Services\Interfaces;

use Albums\Models\Photo;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams photo files through the backend (no direct storage URLs are exposed).
 */
interface PhotoStreamServiceInterface
{
    /**
     * Returns a streamed response for the photo file with proper Content-Type headers.
     *
     * @param Photo $photo
     * @return StreamedResponse
     */
    public function stream(Photo $photo): StreamedResponse;
}
