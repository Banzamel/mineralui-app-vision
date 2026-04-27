<?php

namespace Notifications\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Notifications\Dtos\NotificationDeleteAllDto;

/**
 * Form request for deleting all notifications of the current user.
 */
final class NotificationDeleteAllRequest extends FormRequest
{
    /**
     * Any authenticated user may wipe their own notifications.
     *
     * @return bool always true — the route is already guarded by auth:api
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * No body validation is required for this bulk endpoint.
     *
     * @return array<string, string> empty validation rules
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Builds a DTO with the current user id.
     *
     * @return NotificationDeleteAllDto DTO consumed by the service
     */
    public function getDto(): NotificationDeleteAllDto
    {
        return new NotificationDeleteAllDto(
            userId: (int) $this->user()->id,
        );
    }
}
