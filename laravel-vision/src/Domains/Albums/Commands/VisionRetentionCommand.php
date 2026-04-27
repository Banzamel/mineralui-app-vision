<?php

namespace Albums\Commands;

use Albums\Enums\RetentionPolicyEnum;
use Albums\Services\Interfaces\RetentionServiceInterface;
use Illuminate\Console\Command;

/**
 * Retention command — deletes albums older than the configured retention window.
 * Default window comes from RetentionPolicyEnum::DefaultDays; run by the scheduler once a day.
 */
class VisionRetentionCommand extends Command
{
    /**
     * @var string Command signature — `--days` defaults to the enum value.
     */
    protected $signature = 'vision:albums:retention {--days= : Retention window in days (defaults to RetentionPolicyEnum)}';

    /**
     * @var string Command description.
     */
    protected $description = 'Deletes albums (and their files) older than the configured retention window.';

    /**
     * @param RetentionServiceInterface $retention retention service
     * @return int exit code
     */
    public function handle(RetentionServiceInterface $retention): int
    {
        $days = $this->option('days') !== null
            ? (int) $this->option('days')
            : RetentionPolicyEnum::DefaultDays->value;

        $removed = $retention->purge($days);
        $this->info("Removed {$removed} old albums (window: {$days} days).");
        return self::SUCCESS;
    }
}
