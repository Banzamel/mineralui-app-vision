<?php

namespace System\Requests;

use Illuminate\Foundation\Http\FormRequest;
use System\Dtos\SystemStatusQueryDto;

/**
 * Form request for the system-status endpoint — pulls the tenant scope from the authenticated user.
 */
final class SystemStatusRequest extends FormRequest
{
    /**
     * Any authenticated user with an active company may read system status.
     *
     * @return bool always true — the route is already guarded by auth:api + company.active
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * No body validation is required for this read-only endpoint.
     *
     * @return array<string, string> empty validation rules
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Builds a DTO scoping the query to the current user's company.
     *
     * @return SystemStatusQueryDto DTO consumed by the service
     */
    public function getDto(): SystemStatusQueryDto
    {
        return new SystemStatusQueryDto(
            companyId: (int) $this->user()->company_id,
        );
    }
}
