<?php

namespace FileManager\Requests;

use FileManager\Dtos\UploadFileDto;
use FileManager\Enums\StoragesEnum;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for uploading a new file to the caller's tenant.
 */
final class UploadFileRequest extends FormRequest
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
            'file' => ['required', 'file'],
            'parent_id' => ['nullable', 'integer', 'exists:mgr_file_paths,id'],
            'storage' => ['nullable', 'string', 'in:' . implode(',', array_column(StoragesEnum::cases(), 'value'))],
        ];
    }

    /**
     * @return UploadFileDto DTO consumed by the service
     */
    public function getDto(): UploadFileDto
    {
        return new UploadFileDto(
            companyId: (int) $this->user()->company_id,
            file: $this->file('file'),
            parentId: $this->input('parent_id') !== null ? (int) $this->input('parent_id') : null,
            storage: $this->input('storage') !== null ? (string) $this->input('storage') : null,
        );
    }
}
