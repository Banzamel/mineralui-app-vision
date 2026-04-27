<?php

namespace App\Http\Controllers\Install;

use Illuminate\Http\JsonResponse;
use Installer\Services\Interfaces\InstallerServiceInterface;

/**
 * POST /install/finalize — closes the installer wizard and blocks it from being run again.
 */
readonly class FinalizeInstallController
{
    /**
     * Injects the installer service.
     *
     * @param InstallerServiceInterface $installer Installation wizard service.
     */
    public function __construct(private InstallerServiceInterface $installer) {}

    /**
     * Finalizes the installation (sets the appropriate state in system files) and returns the status.
     *
     * @return JsonResponse Current installer status after closing.
     */
    public function __invoke(): JsonResponse
    {
        $this->installer->finalize();

        return response()->json($this->installer->status());
    }
}
