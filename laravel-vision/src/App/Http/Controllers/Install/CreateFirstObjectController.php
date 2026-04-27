<?php

namespace App\Http\Controllers\Install;

use Illuminate\Http\JsonResponse;
use Installer\Requests\CreateFirstObjectRequest;
use Installer\Services\Interfaces\InstallerServiceInterface;
use RuntimeException;

/**
 * POST /install/first-object — creates the first root object in the Vision tree during installation.
 */
readonly class CreateFirstObjectController
{
    /**
     * Injects the installer service.
     *
     * @param InstallerServiceInterface $installer Installation wizard service.
     */
    public function __construct(private InstallerServiceInterface $installer) {}

    /**
     * Saves the first root object and returns the status together with the result.
     *
     * @param CreateFirstObjectRequest $request Validated first object data.
     * @return JsonResponse Installer status with the created object data, or a 422 error message.
     */
    public function __invoke(CreateFirstObjectRequest $request): JsonResponse
    {
        try {
            $result = $this->installer->saveFirstObject($request->getDto());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($this->installer->status() + $result);
    }
}
