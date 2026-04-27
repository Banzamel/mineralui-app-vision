<?php

namespace App\Http\Controllers\Install;

use Illuminate\Http\JsonResponse;
use Installer\Requests\SaveDatabaseRequest;
use Installer\Services\Interfaces\InstallerServiceInterface;
use RuntimeException;

/**
 * POST /install/database — stores the database credentials and runs migrations.
 */
readonly class SaveDatabaseController
{
    /**
     * Injects the installer service.
     *
     * @param InstallerServiceInterface $installer Installation wizard service.
     */
    public function __construct(private InstallerServiceInterface $installer) {}

    /**
     * Saves the connection settings to the .env file and runs the database migrations.
     *
     * @param SaveDatabaseRequest $request Validated database credentials.
     * @return JsonResponse Current installer status after the save.
     */
    public function __invoke(SaveDatabaseRequest $request): JsonResponse
    {
        try {
            $this->installer->saveDatabase($request->getDto());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($this->installer->status());
    }
}
