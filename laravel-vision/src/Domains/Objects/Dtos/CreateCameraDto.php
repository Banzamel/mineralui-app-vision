<?php

namespace Objects\Dtos;

/**
 * Data bundle for creating a new camera under an object.
 * The RTSP password comes in plaintext — the cameras service encrypts it on save.
 */
final readonly class CreateCameraDto
{
    /**
     * @param int $objectId ID of the object the camera attaches to.
     * @param string $name Technical name (unique slug source).
     * @param string|null $displayName Friendly name shown in the UI.
     * @param string|null $address Physical address, if different from the object.
     * @param string|null $ip Camera IP address (for diagnostics).
     * @param string $streamUrl Full RTSP/HTTP stream URL.
     * @param string|null $streamLogin Stream login (optional).
     * @param string|null $streamPassword Plaintext password — the service will encrypt it.
     * @param string|null $mainPhotoPath Storage path to the thumbnail.
     * @param bool $motionPreviewEnabled Whether the album view exposes the motion-preview toggle.
     */
    public function __construct(
        public int $objectId,
        public string $name,
        public ?string $displayName = null,
        public ?string $address = null,
        public ?string $ip = null,
        public string $streamUrl = '',
        public ?string $streamLogin = null,
        public ?string $streamPassword = null,
        public ?string $mainPhotoPath = null,
        public bool $motionPreviewEnabled = false,
    ) {
    }
}
