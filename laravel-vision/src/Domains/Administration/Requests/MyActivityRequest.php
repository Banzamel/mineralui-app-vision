<?php

namespace Administration\Requests;

use Administration\Dtos\MyActivityQueryDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for listing the caller's own recent activity.
 */
final class MyActivityRequest extends FormRequest
{
    private const int DEFAULT_LIMIT = 100;
    private const int MAX_LIMIT = 500;

    /**
     * @return bool always true — any authenticated user may read their own activity
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, string> validation rules
     */
    public function rules(): array
    {
        return [
            'limit' => 'sometimes|integer|min:1|max:' . self::MAX_LIMIT,
        ];
    }

    /**
     * @return MyActivityQueryDto DTO consumed by the service
     */
    public function getDto(): MyActivityQueryDto
    {
        return new MyActivityQueryDto(
            userId: (int) $this->user()->id,
            limit: (int) $this->input('limit', self::DEFAULT_LIMIT),
        );
    }
}
