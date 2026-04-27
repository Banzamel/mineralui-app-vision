<?php

namespace Objects\Enums;

/**
 * List of object types in the Vision tree.
 * Used for validating frontend input and as a label on the object list/card.
 */
enum ObjectType: string
{
    case Block = 'block';
    case Apartment = 'apartment';
    case House = 'house';
    case Hangar = 'hangar';
    case Garage = 'garage';
    case Other = 'other';

    /**
     * Returns all values as plain strings — handy for validator rules.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
