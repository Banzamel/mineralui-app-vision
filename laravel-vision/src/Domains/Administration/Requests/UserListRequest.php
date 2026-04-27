<?php

namespace Administration\Requests;

use Administration\Dtos\UserListQueryDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for listing tenant users with pagination and filters.
 */
final class UserListRequest extends FormRequest
{
    private const int DEFAULT_PER_PAGE = 15;
    private const int MAX_PER_PAGE = 100;

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
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:' . self::MAX_PER_PAGE,
            'search' => 'sometimes|nullable|string|max:255',
            'role' => 'sometimes|nullable|string|max:255',
            'is_active' => 'sometimes|nullable|boolean',
            'sort_by' => 'sometimes|nullable|string|in:name,email,created_at',
            'sort_order' => 'sometimes|nullable|string|in:asc,desc',
        ];
    }

    /**
     * @return UserListQueryDto DTO consumed by the service
     */
    public function getDto(): UserListQueryDto
    {
        return new UserListQueryDto(
            companyId: (int) $this->user()->company_id,
            perPage: (int) $this->input('limit', self::DEFAULT_PER_PAGE),
            search: $this->input('search') !== null ? (string) $this->input('search') : null,
            role: $this->input('role') !== null ? (string) $this->input('role') : null,
            isActive: $this->has('is_active') ? $this->boolean('is_active') : null,
            sortBy: $this->input('sort_by') !== null ? (string) $this->input('sort_by') : null,
            sortOrder: (string) $this->input('sort_order', 'asc'),
        );
    }
}
