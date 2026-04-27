<?php

namespace Administration\Requests;

use Administration\Dtos\CompanyActivityQueryDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for listing recent tenant-wide activity entries.
 */
final class CompanyActivityRequest extends FormRequest
{
    private const int DEFAULT_LIMIT = 200;
    private const int MAX_LIMIT = 500;

    /**
     * @return bool true when the caller holds the users.view permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('users.view') === true;
    }

    /**
     * @return array<string, string> validation rules
     */
    public function rules(): array
    {
        return [
            'limit' => 'sometimes|integer|min:1|max:' . self::MAX_LIMIT,
        ];
    }

    /**
     * @return CompanyActivityQueryDto DTO consumed by the service
     */
    public function getDto(): CompanyActivityQueryDto
    {
        return new CompanyActivityQueryDto(
            companyId: (int) $this->user()->company_id,
            limit: (int) $this->input('limit', self::DEFAULT_LIMIT),
        );
    }
}
