<?php

namespace FileManager\Requests;

use FileManager\Dtos\FileShowDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for fetching a single file manager item by its route id.
 */
final class FileShowRequest extends FormRequest
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
            'pathId' => 'required|integer|min:1',
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
     * @return FileShowDto DTO consumed by the service
     */
    public function getDto(): FileShowDto
    {
        return new FileShowDto(
            pathId: (int) $this->route('pathId'),
        );
    }
}
