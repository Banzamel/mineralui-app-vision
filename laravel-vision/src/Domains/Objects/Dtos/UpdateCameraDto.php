<?php

namespace Objects\Dtos;

/**
 * Data bundle for editing an existing camera.
 * All fields are optional. If $streamPassword is provided — it gets re-encrypted.
 */
final readonly class UpdateCameraDto
{
    /**
     * @param int|null $objectId New parent object.
     * @param string|null $name New technical name.
     * @param string|null $displayName New display name.
     * @param string|null $address New address.
     * @param string|null $ip New IP address.
     * @param string|null $streamUrl New stream URL.
     * @param string|null $streamLogin New RTSP login.
     * @param string|null $streamPassword New plaintext RTSP password.
     * @param string|null $mainPhotoPath New thumbnail path.
     */
    public function __construct(
        public ?int $objectId = null,
        public ?string $name = null,
        public ?string $displayName = null,
        public ?string $address = null,
        public ?string $ip = null,
        public ?string $streamUrl = null,
        public ?string $streamLogin = null,
        public ?string $streamPassword = null,
        public ?string $mainPhotoPath = null,
    ) {
    }
}
