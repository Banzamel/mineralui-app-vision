<?php

namespace Administration\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Administration\Dtos\UserCreateDto;

/**
 * Form request for creating a new user.
 * Verifies the caller's permissions and validates the input data.
 */
final class UserCreateRequest extends FormRequest
{
    /**
     * Checks whether the authenticated user is allowed to create new users.
     *
     * @return bool true when the user holds the users.create permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('users.create') === true;
    }

    /**
     * Returns the validation rules for the user creation form fields.
     *
     * @return array<string, string> validation rules (field => rule)
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:sec_users,email',
            'password' => 'required|string|min:8',
            'role_name' => 'required|string|exists:auth_roles,name',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Builds a DTO from the validated request data.
     *
     * @return UserCreateDto DTO containing the new user's data
     */
    public function getDto(): UserCreateDto
    {
        return new UserCreateDto(
            name: $this->input('name'),
            email: $this->input('email'),
            password: $this->input('password'),
            roleName: $this->input('role_name'),
            isActive: $this->boolean('is_active', true),
        );
    }
}
