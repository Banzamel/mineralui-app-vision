<?php

namespace Albums\Requests;

use Albums\Dtos\PhotoListDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for listing photos in an album with cursor pagination.
 */
final class PhotoListRequest extends FormRequest
{
    private const int DEFAULT_PER_PAGE = 50;
    private const int MAX_PER_PAGE = 200;

    /**
     * @return bool true when the caller holds the albums.view permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('albums.view') === true;
    }

    /**
     * @return array<string, array<int, string>> validation rules
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:' . self::MAX_PER_PAGE],
            'cursor' => ['sometimes', 'nullable', 'string', 'max:1024'],
        ];
    }

    /**
     * @return array<string, mixed> validation input merged with the route album id
     */
    public function validationData(): array
    {
        return array_merge($this->all(), ['id' => $this->route('id')]);
    }

    /**
     * @return PhotoListDto DTO consumed by the service
     */
    public function getDto(): PhotoListDto
    {
        return new PhotoListDto(
            albumId: (int) $this->route('id'),
            perPage: (int) $this->input('limit', self::DEFAULT_PER_PAGE),
            cursor: $this->input('cursor') !== null ? (string) $this->input('cursor') : null,
        );
    }
}
