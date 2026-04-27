<?php

namespace Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validating user login data (email and password).
 */
final class LoginRequest extends FormRequest
{
    /**
     * Determines whether the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Returns the validation rules for the login request.
     * We deliberately skip `exists:sec_users,email` — credential-level errors flow through the
     * AuthorizationService so the frontend gets a uniform "Invalid credentials" response instead of
     * leaking which emails exist in the database (account enumeration hardening).
     *
     * @return array<string, string> Validation rules for the email and password fields
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];
    }

    /**
     * Builds a LoginDto object from the validated request data.
     *
     * @return \Auth\Dtos\LoginDto Object containing login data
     */
    public function getDto(): \Auth\Dtos\LoginDto
    {
        return new \Auth\Dtos\LoginDto(
            $this->input('email'),
            $this->input('password')
        );
    }
}
