<?php

namespace FileManager\Requests;

use FileManager\Dtos\UpdateItemDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for renaming/moving a file manager item.
 */
final class UpdateItemRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the files.update permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('files.update') === true;
    }

    /**
     * @return array<string, array<int, string>> validation rules
     */
    public function rules(): array
    {
        return [
            'pathId' => ['required', 'integer', 'min:1'],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:mgr_file_paths,id'],
        ];
    }

    /**
     * @return array<string, mixed> validation input merged with the route path id
     */
    public function validationData(): array
    {
        return array_merge($this->all(), ['pathId' => $this->route('pathId')]);
    }

    /**
     * @return UpdateItemDto DTO consumed by the service
     */
    public function getDto(): UpdateItemDto
    {
        return new UpdateItemDto(
            pathId: (int) $this->route('pathId'),
            name: $this->input('name') !== null ? (string) $this->input('name') : null,
            parentId: $this->has('parent_id') && $this->input('parent_id') !== null ? (int) $this->input('parent_id') : null,
        );
    }
}
