<?php

namespace Installer\Services;

use Administration\Models\Company;
use Auth\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Installer\Dtos\AdminDto;
use Installer\Dtos\FirstObjectDto;
use Installer\Services\Interfaces\CompanyProvisioningServiceInterface;
use Objects\Models\VisionObject;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Service that creates the first company's full data set during installation - company, Administrator role, admin account.
 */
class CompanyProvisioningService implements CompanyProvisioningServiceInterface
{
    /**
     * @inheritDoc
     */
    public function provision(FirstObjectDto $object, AdminDto $admin): array
    {
        $company = Company::create([
            'name' => $object->name,
            'slug' => $this->uniqueSlug($object->name),
            'address' => $object->address,
            'is_active' => true,
        ]);

        $role = $this->createAdministratorRole($company->id);
        $user = $this->createAdminUser($company, $admin);
        $user->assignRole($role);

        $rootObject = $this->createRootObject($company->id, $object);

        return [
            'company' => $company,
            'role' => $role,
            'user' => $user,
            'object' => $rootObject,
        ];
    }

    /**
     * Creates the root VisionObject for a freshly provisioned company.
     * Slug is derived from the name with a numeric suffix in case of collision (defensive — first row should always be free).
     *
     * @param int $companyId New company ID.
     * @param FirstObjectDto $object First object data from the wizard.
     * @return VisionObject Newly created root object.
     */
    private function createRootObject(int $companyId, FirstObjectDto $object): VisionObject
    {
        return VisionObject::create([
            'company_id' => $companyId,
            'parent_id' => null,
            'name' => $object->name,
            'slug' => $this->uniqueObjectSlug($companyId, $object->name),
            'type' => $object->type,
            'address' => $object->address,
            'description' => $object->description,
            'depth' => 0,
        ]);
    }

    /**
     * @inheritDoc
     *
     * Creates the Administrator role for the company and assigns it every permission from the api guard.
     */
    public function createAdministratorRole(int $companyId): Role
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($companyId);

        $role = Role::firstOrCreate([
            'name' => 'Administrator',
            'guard_name' => 'api',
            'company_id' => $companyId,
        ]);

        $allPermissions = Permission::where('guard_name', 'api')->pluck('name')->all();
        $role->syncPermissions($allPermissions);

        return $role;
    }

    /**
     * @inheritDoc
     */
    public function createAdminUser(Company $company, AdminDto $admin): User
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);

        return User::create([
            'company_id' => $company->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => Hash::make($admin->password),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * Generates a unique company slug based on the name - appends a numeric suffix when the slug is already taken.
     *
     * @param string $name Company name.
     * @return string Unique slug.
     */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'company';
        $slug = $base;
        $i = 2;

        while (Company::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /**
     * Generates a unique VisionObject slug within the given company.
     *
     * @param int $companyId Company scope.
     * @param string $name Object name to slugify.
     * @return string Slug guaranteed to be free in the company scope.
     */
    private function uniqueObjectSlug(int $companyId, string $name): string
    {
        $base = Str::slug($name) ?: 'object';
        $slug = $base;
        $i = 2;

        while (VisionObject::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
