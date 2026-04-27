<?php

namespace Albums\Services\Interfaces;

/**
 * Retention service — purges albums older than a given threshold along with their files.
 * Driven by the scheduler.
 */
interface RetentionServiceInterface
{
    /**
     * Removes albums older than $days days together with their photos and FileManager directory.
     *
     * @param int $days
     * @return int Number of albums removed.
     */
    public function purge(int $days): int;
}
