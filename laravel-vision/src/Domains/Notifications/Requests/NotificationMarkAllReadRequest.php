<?php

namespace Notifications\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Notifications\Dtos\NotificationMarkAllReadDto;

/**
 * Form request for marking all unread notifications of the current user as read.
 */
final class NotificationMarkAllReadRequest extends FormRequest
{
    /**
     * Any authenticated user may mark their own notifications as read.
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
     * @return NotificationMarkAllReadDto DTO consumed by the service
     */
    public function getDto(): NotificationMarkAllReadDto
    {
        return new NotificationMarkAllReadDto(
            userId: (int) $this->user()->id,
        );
    }
}
