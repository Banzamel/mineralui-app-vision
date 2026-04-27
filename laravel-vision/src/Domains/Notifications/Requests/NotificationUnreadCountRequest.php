<?php

namespace Notifications\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Notifications\Dtos\NotificationUnreadCountDto;

/**
 * Form request for fetching the unread-notifications count of the current user.
 */
final class NotificationUnreadCountRequest extends FormRequest
{
    /**
     * Any authenticated user may read their own unread counter.
     *
     * @return bool always true — the route is already guarded by auth:api
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * No body validation is required for this read-only endpoint.
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
     * @return NotificationUnreadCountDto DTO consumed by the service
     */
    public function getDto(): NotificationUnreadCountDto
    {
        return new NotificationUnreadCountDto(
            userId: (int) $this->user()->id,
        );
    }
}
