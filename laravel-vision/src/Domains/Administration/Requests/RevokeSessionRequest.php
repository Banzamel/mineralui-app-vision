<?php

namespace Administration\Requests;

use Administration\Dtos\RevokeSessionDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for revoking a single Passport session of a user in the caller's tenant.
 */
final class RevokeSessionRequest extends FormRequest
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
            'session' => 'required|string',
        ];
    }

    /**
     * @return array<string, mixed> validation input merged with the route parameters
     */
    public function validationData(): array
    {
        return array_merge($this->all(), [
            'user' => $this->route('user'),
            'session' => $this->route('session'),
        ]);
    }

    /**
     * @return RevokeSessionDto DTO consumed by the service
     */
    public function getDto(): RevokeSessionDto
    {
        return new RevokeSessionDto(
            companyId: (int) $this->user()->company_id,
            userId: (int) $this->route('user'),
            sessionId: (string) $this->route('session'),
        );
    }
}
