<?php

namespace Albums\Enums;

/**
 * Retention policy knobs for album cleanup — centralised so scheduler, RetentionService and
 * potential admin tooling share the same source of truth.
 */
enum RetentionPolicyEnum: int
{
    /**
     * Default retention window in days — albums older than this are purged by the retention command.
     */
    case DefaultDays = 7;
}
