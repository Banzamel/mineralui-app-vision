<?php

namespace Administration\Requests;

use Administration\Dtos\ResetUserPasswordDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for resetting a user's password — captures IP and user-agent for the audit log.
 */
final class ResetUserPasswordRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the users.update permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('users.update') === true;
    }

    /**
     * @return array<string, string> validation rules
     */
    public function rules(): array
    {
        return [
            'user' => 'required|integer|min:1',
        ];
    }

    /**
     * @return array<string, mixed> validation input merged with the route user id
     */
    public function validationData(): array
    {
        return array_merge($this->all(), ['user' => $this->route('user')]);
    }

    /**
     * @return ResetUserPasswordDto DTO consumed by the service
     */
    public function getDto(): ResetUserPasswordDto
    {
        return new ResetUserPasswordDto(
            userId: (int) $this->route('user'),
            ip: $this->ip(),
            userAgent: $this->userAgent(),
        );
    }
}
