<?php

namespace Administration\Requests;

use Administration\Dtos\UserShowDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for fetching a single user by the {user} route parameter.
 */
final class UserShowRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the users.view permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('users.view') === true;
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
     * @return UserShowDto DTO consumed by the service
     */
    public function getDto(): UserShowDto
    {
        return new UserShowDto(
            userId: (int) $this->route('user'),
        );
    }
}
