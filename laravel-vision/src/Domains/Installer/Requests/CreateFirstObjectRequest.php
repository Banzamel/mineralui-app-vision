<?php

namespace Installer\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Installer\Dtos\FirstObjectDto;

/**
 * Request validating first object (building/company) data in the install wizard.
 */
final class CreateFirstObjectRequest extends FormRequest
{
    /**
     * The installer endpoint is public.
     *
     * @return bool Always true.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * First object validation rules.
     *
     * @return array<string, string> Validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:block,apartment,house,hangar,garage,other',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Builds a DTO with the first object data.
     *
     * @return FirstObjectDto Object DTO.
     */
    public function getDto(): FirstObjectDto
    {
        return new FirstObjectDto(
            name: $this->input('name'),
            type: $this->input('type'),
            address: $this->input('address'),
            description: $this->input('description'),
        );
    }
}
