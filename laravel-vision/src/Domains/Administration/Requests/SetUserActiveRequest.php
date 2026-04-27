<?php

namespace Administration\Requests;

use Administration\Dtos\SetUserActiveDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for toggling a user's active flag.
 */
final class SetUserActiveRequest extends FormRequest
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
            'is_active' => 'required|boolean',
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
     * @return SetUserActiveDto DTO consumed by the service
     */
    public function getDto(): SetUserActiveDto
    {
        return new SetUserActiveDto(
            userId: (int) $this->route('user'),
            active: $this->boolean('is_active'),
        );
    }
}
