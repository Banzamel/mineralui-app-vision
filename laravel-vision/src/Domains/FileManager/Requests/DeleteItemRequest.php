<?php

namespace FileManager\Requests;

use FileManager\Dtos\DeleteItemDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for deleting a file manager item.
 */
final class DeleteItemRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the files.delete permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('files.delete') === true;
    }

    /**
     * @return array<string, array<int, string>> validation rules
     */
    public function rules(): array
    {
        return [
            'pathId' => ['required', 'integer', 'min:1'],
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
     * @return DeleteItemDto DTO consumed by the service
     */
    public function getDto(): DeleteItemDto
    {
        return new DeleteItemDto(
            pathId: (int) $this->route('pathId'),
        );
    }
}
