<?php

namespace Notifications\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Notifications\Dtos\NotificationDeleteDto;

/**
 * Form request for deleting a single notification.
 * The notification id comes from the `{id}` route parameter; ownership is enforced in the service.
 */
final class NotificationDeleteRequest extends FormRequest
{
    /**
     * Any authenticated user may delete their own notifications.
     *
     * @return bool always true — the route is already guarded by auth:api
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Validates the `{id}` route parameter as a positive integer.
     *
     * @return array<string, string> validation rules (field => rule)
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
        ];
    }

    /**
     * Returns the route parameters so they are included in validation.
     *
     * @return array<string, mixed> validation input with the route id merged in
     */
    public function validationData(): array
    {
        return array_merge($this->all(), ['id' => $this->route('id')]);
    }

    /**
     * Builds a DTO pairing the acting user id with the target notification id.
     *
     * @return NotificationDeleteDto DTO consumed by the service
     */
    public function getDto(): NotificationDeleteDto
    {
        return new NotificationDeleteDto(
            userId: (int) $this->user()->id,
            notificationId: (int) $this->route('id'),
        );
    }
}
