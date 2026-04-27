<?php

namespace Objects\Dtos;

/**
 * Data bundle for editing an existing object.
 * All fields are optional — the service only updates what was provided.
 */
final readonly class UpdateObjectDto
{
    /**
     * @param int|null $parentId New parent in the tree (null = move to root).
     * @param string|null $name New name.
     * @param string|null $type New type (ObjectType enum value).
     * @param string|null $address Address, if changed.
     * @param string|null $description Description, if changed.
     * @param string|null $mainPhotoPath New thumbnail path.
     */
    public function __construct(
        public ?int $parentId = null,
        public ?string $name = null,
        public ?string $type = null,
        public ?string $address = null,
        public ?string $description = null,
        public ?string $mainPhotoPath = null,
    ) {
    }

    /**
     * Array with null values stripped — ready for Model::update().
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'parent_id' => $this->parentId,
            'name' => $this->name,
            'type' => $this->type,
            'address' => $this->address,
            'description' => $this->description,
            'main_photo_path' => $this->mainPhotoPath,
        ], fn ($v) => $v !== null);
    }
}
