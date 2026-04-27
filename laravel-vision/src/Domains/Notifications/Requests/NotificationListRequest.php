<?php

namespace Notifications\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Notifications\Dtos\NotificationListDto;

/**
 * Form request for listing notifications of the currently authenticated user.
 * Accepts an optional `limit` query parameter bounded to a safe range.
 */
final class NotificationListRequest extends FormRequest
{
    /**
     * Default row limit applied when the caller does not provide one.
     *
     * @var int
     */
    private const int DEFAULT_LIMIT = 100;

    /**
     * Maximum row limit the caller is allowed to request.
     *
     * @var int
     */
    private const int MAX_LIMIT = 200;

    /**
     * Any authenticated user may list their own notifications.
     *
     * @return bool always true — the route is already guarded by auth:api
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Returns the validation rules for the list query parameters.
     *
     * @return array<string, string> validation rules (field => rule)
     */
    public function rules(): array
    {
        return [
            'limit' => 'sometimes|integer|min:1|max:' . self::MAX_LIMIT,
        ];
    }

    /**
     * Builds a DTO with the current user id and the resolved row limit.
     *
     * @return NotificationListDto DTO consumed by the service
     */
    public function getDto(): NotificationListDto
    {
        return new NotificationListDto(
            userId: (int) $this->user()->id,
            limit: (int) $this->input('limit', self::DEFAULT_LIMIT),
        );
    }
}
