<?php

namespace App\Http\Controllers\Install;

use Illuminate\Http\JsonResponse;
use Installer\Requests\CreateAdminRequest;
use Installer\Services\Interfaces\InstallerServiceInterface;

/**
 * POST /install/admin — creates the first administrator account during installation.
 */
readonly class CreateAdminController
{
    /**
     * Injects the installer service.
     *
     * @param InstallerServiceInterface $installer Installation wizard service.
     */
    public function __construct(private InstallerServiceInterface $installer) {}

    /**
     * Creates the company administrator account based on data from the wizard.
     *
     * @param CreateAdminRequest $request Validated administrator data.
     * @return JsonResponse Current installer status.
     */
    public function __invoke(CreateAdminRequest $request): JsonResponse
    {
        $this->installer->saveAdmin($request->getDto());

        return response()->json($this->installer->status());
    }
}
