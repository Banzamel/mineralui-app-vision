<?php

namespace Objects\Enums;

/**
 * Type of user scope in Vision — what the user has access to.
 * Building = whole object from the tree, address = address (string), camera = a single camera.
 */
enum ScopeType: string
{
    case Building = 'building';
    case Address = 'address';
    case Camera = 'camera';

    /**
     * Values as an array of strings — for Laravel's validator.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
