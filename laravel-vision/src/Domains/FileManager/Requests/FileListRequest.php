<?php

namespace FileManager\Requests;

use FileManager\Dtos\FileListDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for listing contents of a file manager directory scoped to the caller's tenant.
 */
final class FileListRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the files.view permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('files.view') === true;
    }

    /**
     * @return array<string, string> validation rules
     */
    public function rules(): array
    {
        return [
            'parent_id' => 'sometimes|nullable|integer|exists:mgr_file_paths,id',
        ];
    }

    /**
     * @return FileListDto DTO consumed by the service
     */
    public function getDto(): FileListDto
    {
        $parentId = $this->query('parent_id');
        return new FileListDto(
            companyId: (int) $this->user()->company_id,
            parentId: $parentId !== null ? (int) $parentId : null,
        );
    }
}
