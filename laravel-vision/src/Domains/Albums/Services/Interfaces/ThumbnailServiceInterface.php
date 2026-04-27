<?php

namespace Albums\Services\Interfaces;

use Albums\Models\Photo;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Generates and serves grid-sized thumbnails for photos.
 */
interface ThumbnailServiceInterface
{
    /**
     * Materialises the thumbnail file for a photo if it does not exist yet.
     * Idempotent — safe to call repeatedly from the queue worker.
     *
     * @param Photo $photo Photo whose thumbnail should be generated.
     * @return bool True if a new thumbnail was written, false if it already existed or could not be generated.
     */
    public function generate(Photo $photo): bool;

    /**
     * Streams the thumbnail file. Generates it on the fly when the queued job has not produced it yet.
     *
     * @param Photo $photo Photo to stream.
     * @return StreamedResponse
     */
    public function stream(Photo $photo): StreamedResponse;
}
