<?php

namespace Administration\Requests;

use Administration\Dtos\RoleDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for updating an existing role — name uniqueness excludes the edited role.
 */
final class RoleUpdateRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the permissions.manage permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('roles.manage') === true;
    }

    /**
     * @return array<string, array<int, mixed>> validation rules
     */
    public function rules(): array
    {
        $roleId = (int) $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('auth_roles', 'name')->ignore($roleId),
            ],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'exists:auth_permissions,name'],
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
