<?php

namespace Objects\Dtos;

/**
 * Single visibility scope entry: this user sees this specific resource.
 * A list of such DTOs is saved in bulk by UserScopeService.
 */
final readonly class UserScopeDto
{
    /**
     * @param string $type ScopeType enum value (building/address/camera).
     * @param string $scopeId Resource identifier (e.g. object ID, camera slug, address).
     */
    public function __construct(
        public string $type,
        public string $scopeId,
    ) {
    }
}
