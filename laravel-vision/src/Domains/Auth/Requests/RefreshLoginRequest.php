<?php

namespace Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validating data for refreshing a session (refresh token).
 */
final class RefreshLoginRequest extends FormRequest
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
     * Returns the validation rules for the token refresh request.
     *
     * @return array<string, string> Validation rules for the refresh_token field
     */
    public function rules(): array
    {
        return [
            'refresh_token' => 'required|string',
        ];
    }

    /**
     * Builds a RefreshLoginDto object from the validated request data.
     *
     * @return \Auth\Dtos\RefreshLoginDto Object containing the refresh token
     */
    public function getDto(): \Auth\Dtos\RefreshLoginDto
    {
        return new \Auth\Dtos\RefreshLoginDto(
            $this->input('refresh_token')
        );
    }
}
