<?php

namespace Administration\Requests;

use Administration\Dtos\CompanySessionsQueryDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for listing active Passport sessions across the caller's tenant.
 */
final class CompanySessionsRequest extends FormRequest
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
     * @return CompanySessionsQueryDto DTO consumed by the service
     */
    public function getDto(): CompanySessionsQueryDto
    {
        $token = $this->user()->token();
        return new CompanySessionsQueryDto(
            companyId: (int) $this->user()->company_id,
            currentTokenId: $token?->id !== null ? (string) $token->id : null,
        );
    }
}
