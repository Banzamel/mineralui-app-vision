<?php

namespace Administration\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Administration\Dtos\UserUpdateDto;

/**
 * Form request for updating an existing user's data.
 * Verifies the caller's permissions and validates the input data.
 */
final class UserUpdateRequest extends FormRequest
{
    /**
     * Checks whether the authenticated user is allowed to edit other users.
     *
     * @return bool true when the user holds the users.update permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('users.update') === true;
    }

    /**
     * Returns the validation rules for the user edit form fields (all fields are optional).
     *
     * @return array<string, string> validation rules (field => rule)
     */
    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:sec_users,email,' . $userId,
            'password' => 'sometimes|string|min:8',
            'role_name' => 'sometimes|string|exists:auth_roles,name',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Builds a DTO from the validated request data.
     *
     * @return UserUpdateDto DTO containing the fields to change
     */
    public function getDto(): UserUpdateDto
    {
        return new UserUpdateDto(
            name: $this->input('name'),
            email: $this->input('email'),
            password: $this->input('password'),
            roleName: $this->input('role_name'),
            isActive: $this->has('is_active') ? $this->boolean('is_active') : null,
        );
    }
}
