<?php

namespace App\Http\Controllers\Install;

use Illuminate\Http\JsonResponse;
use Installer\Requests\CreateFirstCameraRequest;
use Installer\Services\Interfaces\InstallerServiceInterface;

/**
 * POST /install/first-camera — adds the first camera to the created object during installation.
 */
readonly class CreateFirstCameraController
{
    /**
     * Injects the installer service.
     *
     * @param InstallerServiceInterface $installer Installation wizard service.
     */
    public function __construct(private InstallerServiceInterface $installer) {}

    /**
     * Saves the first camera and returns the current installer status.
     *
     * @param CreateFirstCameraRequest $request Validated first camera data.
     * @return JsonResponse Current installer status.
     */
    public function __invoke(CreateFirstCameraRequest $request): JsonResponse
    {
        $this->installer->saveFirstCamera($request->getDto());

        return response()->json($this->installer->status());
    }
}
