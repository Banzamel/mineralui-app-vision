<?php

namespace App\Http\Controllers\Install;

use Illuminate\Http\JsonResponse;
use Installer\Resources\InstallStatusResource;
use Installer\Services\Interfaces\InstallerServiceInterface;

/**
 * GET /install/status — returns the current stage of the application installer.
 */
readonly class GetInstallStatusController
{
    /**
     * Injects the installer service.
     *
     * @param InstallerServiceInterface $installer Service responsible for the installation wizard.
     */
    public function __construct(private InstallerServiceInterface $installer) {}

    /**
     * Returns the current installer state (which step the user is on).
     *
     * @return JsonResponse JSON response with the current installation stage.
     */
    public function __invoke(): JsonResponse
    {
        $resource = new InstallStatusResource($this->installer->status());

        return response()->json($resource->resolve());
    }
}
