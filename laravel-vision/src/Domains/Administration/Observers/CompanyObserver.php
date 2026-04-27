<?php

namespace Administration\Observers;

use Administration\Models\Company;

/**
 * Observer for the Company model reacting to lifecycle events (create, update).
 * Placeholder for hooking additional company-related domain logic.
 */
class CompanyObserver
{
    /**
     * Fired automatically just before a new company is persisted to the database.
     * Empty placeholder for domain hooks - the actual company provisioning (default role, file directories)
     * lives in Installer\Services\CompanyProvisioningService and is invoked manually during installation.
     *
     * @param Company $company company about to be created
     * @return void no return value
     */
    public function creating(Company $company): void
    {
        // Placeholder: domain-specific hooks fire here.
        // Vision's own hooks (provisioning default role + file manager path) live in
        // Installer\Services\CompanyProvisioningService and fire explicitly during install.
    }
}
