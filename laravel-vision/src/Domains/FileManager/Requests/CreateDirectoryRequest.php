<?php

namespace FileManager\Requests;

use FileManager\Dtos\CreateDirectoryDto;
use FileManager\Enums\StoragesEnum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for creating a new file manager directory in the caller's tenant.
 */
final class CreateDirectoryRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the files.create permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('files.create') === true;
    }

    /**
     * @return array<string, array<int, string>> validation rules
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:mgr_file_paths,id'],
            'storage' => ['nullable', 'string', 'in:' . implode(',', array_column(StoragesEnum::cases(), 'value'))],
        ];
    }

    /**
     * @return CreateDirectoryDto DTO consumed by the service
     */
    public function getDto(): CreateDirectoryDto
    {
        return new CreateDirectoryDto(
            companyId: (int) $this->user()->company_id,
            name: (string) $this->input('name'),
            parentId: $this->input('parent_id') !== null ? (int) $this->input('parent_id') : null,
            storage: $this->input('storage') !== null ? (string) $this->input('storage') : null,
        );
    }
}
