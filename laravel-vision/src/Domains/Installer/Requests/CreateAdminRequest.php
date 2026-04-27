<?php

namespace Installer\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Installer\Dtos\AdminDto;

/**
 * Request validating administrator account data in the install wizard.
 */
final class CreateAdminRequest extends FormRequest
{
    /**
     * The installer endpoint is public.
     *
     * @return bool Always true.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Administrator account validation rules (password min. 8 characters, must match password_confirmation).
     *
     * @return array<string, string> Validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|max:255|confirmed',
            'password_confirmation' => 'required|string',
        ];
    }

    /**
     * Builds a DTO with administrator data.
     *
     * @return AdminDto Administrator DTO.
     */
    public function getDto(): AdminDto
    {
        return new AdminDto(
            name: $this->input('name'),
            email: $this->input('email'),
            password: $this->input('password'),
        );
    }
}
