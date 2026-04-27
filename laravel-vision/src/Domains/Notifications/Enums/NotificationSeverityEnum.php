<?php

namespace Notifications\Enums;

/**
 * Severity levels supported by notifications — drives the colour/icon in the UI.
 */
enum NotificationSeverityEnum: string
{
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Error = 'error';

    /**
     * Returns the list of string values used in validation rules and DB storage.
     *
     * @return array<int, string> severity string values
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
