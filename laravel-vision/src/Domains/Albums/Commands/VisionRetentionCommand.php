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
    protected $signature = 'vision:albums:retention {--days= : Liczba dni retencji (domyślnie z RetentionPolicyEnum)}';

    /**
     * @var string Command description.
     */
    protected $description = 'Kasuje albumy (i ich pliki) starsze niż ustalony okres retencji.';

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
        $this->info("Skasowano {$removed} starych albumów (próg: {$days} dni).");
        return self::SUCCESS;
    }
}
