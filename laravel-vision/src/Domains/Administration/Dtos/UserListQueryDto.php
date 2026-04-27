<?php

namespace Administration\Dtos;

/**
 * Input DTO for listing tenant users with pagination and filter support.
 */
readonly class UserListQueryDto
{
    /**
     * @param int $companyId tenant id
     * @param int $perPage rows per page
     * @param string|null $search name/email substring
     * @param string|null $role role name filter
     * @param bool|null $isActive active-flag filter (null = no filter)
     * @param string|null $sortBy allowed columns: name, email, created_at
     * @param string $sortOrder asc or desc
     */
    public function __construct(
        private int $companyId,
        private int $perPage,
        private ?string $search = null,
        private ?string $role = null,
        private ?bool $isActive = null,
        private ?string $sortBy = null,
        private string $sortOrder = 'asc',
    ) {}

    /**
     * @return int tenant id
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * @return int rows per page
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @return array<string, mixed> filter bag ready for the repository
     */
    public function toFilters(): array
    {
        return [
            'search' => $this->search,
            'role' => $this->role,
            'is_active' => $this->isActive,
            'sort_by' => $this->sortBy,
            'sort_order' => $this->sortOrder,
        ];
    }
}
