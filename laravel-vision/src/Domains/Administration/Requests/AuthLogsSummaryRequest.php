<?php

namespace Administration\Requests;

use Administration\Dtos\AuthLogsSummaryQueryDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for the auth-logs summary panel — scopes the query to the caller's tenant.
 */
final class AuthLogsSummaryRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the users.view permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('users.view') === true;
    }

    /**
     * @return array<string, string> empty validation rules (no body)
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return AuthLogsSummaryQueryDto DTO consumed by the service
     */
    public function getDto(): AuthLogsSummaryQueryDto
    {
        return new AuthLogsSummaryQueryDto(
            companyId: (int) $this->user()->company_id,
        );
    }
}
