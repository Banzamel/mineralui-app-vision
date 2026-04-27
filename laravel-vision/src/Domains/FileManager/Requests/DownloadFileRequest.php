<?php

namespace FileManager\Requests;

use FileManager\Dtos\DownloadFileDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for downloading a file manager item.
 */
final class DownloadFileRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the files.view permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('files.view') === true;
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
     * @return DownloadFileDto DTO consumed by the service
     */
    public function getDto(): DownloadFileDto
    {
        return new DownloadFileDto(
            pathId: (int) $this->route('pathId'),
        );
    }
}
