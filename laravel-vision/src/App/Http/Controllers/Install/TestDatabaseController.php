<?php

namespace App\Http\Controllers\Install;

use Illuminate\Http\JsonResponse;
use Installer\Requests\TestDatabaseRequest;
use Installer\Services\Interfaces\InstallerServiceInterface;
use RuntimeException;

/**
 * POST /install/test-database — verifies the supplied database credentials.
 */
readonly class TestDatabaseController
{
    /**
     * Injects the installer service.
     *
     * @param InstallerServiceInterface $installer Installation wizard service.
     */
    public function __construct(private InstallerServiceInterface $installer) {}

    /**
     * Attempts to connect to the database with the supplied parameters.
     *
     * @param TestDatabaseRequest $request Validated database credentials.
     * @return JsonResponse OK when the connection succeeds, otherwise a 422 error message.
     */
    public function __invoke(TestDatabaseRequest $request): JsonResponse
    {
        try {
            $this->installer->testDatabase($request->getDto());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true]);
    }
}
