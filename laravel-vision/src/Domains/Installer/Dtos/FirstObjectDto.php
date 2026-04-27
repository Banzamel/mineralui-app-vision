<?php

namespace Installer\Dtos;

/**
 * DTO with the data of the first object (company/building) created during installation.
 */
final readonly class FirstObjectDto
{
    /**
     * @param string $name Object name (also the company name).
     * @param string $type Object type (e.g. building, address).
     * @param string|null $address Postal address (optional).
     * @param string|null $description Additional description (optional).
     */
    public function __construct(
        public string $name,
        public string $type,
        public ?string $address = null,
        public ?string $description = null,
    ) {}

    /**
     * Returns the data as an associative array.
     *
     * @return array{name: string, type: string, address: string|null, description: string|null}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'address' => $this->address,
            'description' => $this->description,
        ];
    }
}
