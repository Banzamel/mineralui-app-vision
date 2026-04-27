<?php

namespace Administration\Requests;

use Administration\Dtos\RoleDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for creating a new role — enforces a unique name across auth_roles.
 */
final class RoleCreateRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the permissions.manage permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('roles.manage') === true;
    }

    /**
     * @return array<string, string> validation rules
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:auth_roles,name',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|exists:auth_permissions,name',
        ];
    }

    /**
     * @return RoleDto DTO consumed by the service
     */
    public function getDto(): RoleDto
    {
        return new RoleDto(
            name: (string) $this->input('name'),
            permissions: (array) $this->input('permissions'),
        );
    }
}
