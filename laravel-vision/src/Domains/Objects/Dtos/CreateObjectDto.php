<?php

namespace Objects\Dtos;

/**
 * Data bundle for creating a new object in the Vision tree.
 * Fields match columns in the vision_objects table — without company_id, which the service appends from the current company.
 */
final readonly class CreateObjectDto
{
    /**
     * @param int|null $parentId ID of the parent object in the tree, or null for a root.
     * @param string $name Name shown in the list.
     * @param string $type Object type (ObjectType enum value as string).
     * @param string|null $address Building address, if applicable.
     * @param string|null $description Short user-supplied description.
     * @param string|null $mainPhotoPath Storage path to the thumbnail.
     */
    public function __construct(
        public ?int $parentId,
        public string $name,
        public string $type,
        public ?string $address = null,
        public ?string $description = null,
        public ?string $mainPhotoPath = null,
    ) {
    }

    /**
     * Field array ready to feed straight into Model::create().
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'parent_id' => $this->parentId,
            'name' => $this->name,
            'type' => $this->type,
            'address' => $this->address,
            'description' => $this->description,
            'main_photo_path' => $this->mainPhotoPath,
        ];
    }
}
