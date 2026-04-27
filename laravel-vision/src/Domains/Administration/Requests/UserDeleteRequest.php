<?php

namespace Administration\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for deleting a user — just validates the route id and the caller's permission.
 */
final class UserDeleteRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the users.delete permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('users.delete') === true;
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
     * @return int target user id resolved from the route
     */
    public function getUserId(): int
    {
        return (int) $this->route('user');
    }
}
