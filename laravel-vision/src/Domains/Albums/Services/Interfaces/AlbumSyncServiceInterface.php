<?php

namespace Albums\Services\Interfaces;

use Objects\Models\Camera;

/**
 * Synchronizes camera storage directories with the albums/photos tables.
 * Daily directory naming is supported in both YYYY-MM-DD and DD-MM-YYYY forms.
 */
interface AlbumSyncServiceInterface
{
    /**
     * Scans the directory tree of a single camera and adds missing albums/photos.
     *
     * @param Camera $camera
     * @return int Number of new photos added.
     */
    public function syncCamera(Camera $camera): int;

    /**
     * Runs syncCamera() for every camera in the database.
     *
     * @return int Total number of new photos added across all cameras.
     */
    public function syncAll(): int;
}
