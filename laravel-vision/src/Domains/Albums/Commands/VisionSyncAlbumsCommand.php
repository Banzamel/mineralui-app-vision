<?php

namespace Albums\Commands;

use Albums\Services\Interfaces\AlbumSyncServiceInterface;
use Illuminate\Console\Command;

/**
 * Console command run by the scheduler every 5–15 minutes.
 * Iterates over all cameras and adds to the database any new photos found in storage.
 */
class VisionSyncAlbumsCommand extends Command
{
    /**
     * @var string Command signature for invocation through artisan.
     */
    protected $signature = 'vision:albums:sync';

    /**
     * @var string Command description shown in `php artisan list`.
     */
    protected $description = 'Scans camera directories and syncs albums/photos into the database.';

    /**
     * Runs the synchronization.
     *
     * @param AlbumSyncServiceInterface $sync Albums synchronization service.
     * @return int Exit code (0 = OK).
     */
    public function handle(AlbumSyncServiceInterface $sync): int
    {
        $added = $sync->syncAll();
        $this->info("Added {$added} new photos.");
        return self::SUCCESS;
    }
}
