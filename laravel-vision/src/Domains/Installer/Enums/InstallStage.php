<?php

namespace Installer\Enums;

/**
 * Installation wizard stages - from a fresh application up to a completed install.
 */
enum InstallStage: string
{
    case Fresh = 'fresh';
    case Database = 'database';
    case Admin = 'admin';
    case Object = 'object';
    case Camera = 'camera';
    case Finalized = 'finalized';

    /**
     * Checks whether this stage comes later in the flow than the given one.
     *
     * @param self $other Stage to compare with.
     * @return bool True if this stage is further along.
     */
    public function isAfter(self $other): bool
    {
        return $this->order() > $other->order();
    }

    /**
     * Returns the numeric order of the stage used for comparisons.
     *
     * @return int Order (0..5).
     */
    public function order(): int
    {
        return match ($this) {
            self::Fresh => 0,
            self::Database => 1,
            self::Admin => 2,
            self::Object => 3,
            self::Camera => 4,
            self::Finalized => 5,
        };
    }
}
