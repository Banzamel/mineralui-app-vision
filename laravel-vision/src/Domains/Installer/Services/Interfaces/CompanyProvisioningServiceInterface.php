<?php

namespace Installer\Services\Interfaces;

use Administration\Models\Company;
use Auth\Models\User;
use Installer\Dtos\AdminDto;
use Installer\Dtos\FirstObjectDto;
use Objects\Models\VisionObject;

/**
 * Contract for the service that creates a new company along with the administrator role and admin account.
 */
interface CompanyProvisioningServiceInterface
{
    /**
     * Creates a company along with the Administrator role, admin user and the root tree object.
     *
     * @param FirstObjectDto $object First object data (also used for company name/address).
     * @param AdminDto $admin Administrator data.
     * @return array{company: Company, role: \Spatie\Permission\Models\Role, user: User, object: VisionObject} Created entities.
     */
    public function provision(FirstObjectDto $object, AdminDto $admin): array;

    /**
     * Creates the Administrator role for the given company along with the full set of permissions.
     *
     * @param int $companyId Company ID.
     * @return \Spatie\Permission\Models\Role Created role.
     */
    public function createAdministratorRole(int $companyId): \Spatie\Permission\Models\Role;

    /**
     * Creates the administrator account in the company.
     *
     * @param Company $company Company.
     * @param AdminDto $admin Admin account data.
     * @return User Created user.
     */
    public function createAdminUser(Company $company, AdminDto $admin): User;
}
