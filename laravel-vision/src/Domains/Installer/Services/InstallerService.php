<?php

namespace Installer\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Passport\Client as PassportClient;
use Installer\Dtos\AdminDto;
use Installer\Dtos\DatabaseConfigDto;
use Installer\Dtos\FirstCameraDto;
use Installer\Dtos\FirstObjectDto;
use Installer\Enums\InstallStage;
use Installer\Events\InstallationCompleted;
use Installer\Repositories\Interfaces\InstallStateRepositoryInterface;
use Installer\Services\Interfaces\CompanyProvisioningServiceInterface;
use Installer\Services\Interfaces\DatabaseTesterServiceInterface;
use Installer\Services\Interfaces\EnvWriterServiceInterface;
use Installer\Services\Interfaces\InstallerServiceInterface;
use FileManager\Services\Interfaces\FileManagerServiceInterface;
use Objects\Models\Camera;
use Objects\Models\VisionObject;
use RuntimeException;

/**
 * Main installation wizard service - combines database config saving, admin data and the first camera into a coherent flow.
 */
class InstallerService implements InstallerServiceInterface
{
    /**
     * Injects every dependency required by the install wizard.
     *
     * @param InstallStateRepositoryInterface $state Install state repository.
     * @param DatabaseTesterServiceInterface $databaseTester Service for testing connection and running migrations.
     * @param EnvWriterServiceInterface $envWriter Service for writing the .env file.
     * @param CompanyProvisioningServiceInterface $provisioning Service that creates the company with initial data.
     */
    public function __construct(
        private readonly InstallStateRepositoryInterface $state,
        private readonly DatabaseTesterServiceInterface $databaseTester,
        private readonly EnvWriterServiceInterface $envWriter,
        private readonly CompanyProvisioningServiceInterface $provisioning,
        private readonly FileManagerServiceInterface $fileManager,
    ) {}

    /**
     * @inheritDoc
     */
    public function status(): array
    {
        return [
            'installed' => $this->state->isInstalled(),
            'stage' => $this->state->getStage()->value,
            // Dev auto-fill for the first wizard step — full data from .env.
            // Endpoint is guarded by install.gate (available only before installation),
            // so the password leaves the backend exactly once and only for the local installer.
            'database_defaults' => [
                'host' => (string) config('database.connections.mysql.host', '127.0.0.1'),
                'port' => (int) config('database.connections.mysql.port', 3306),
                'database' => (string) config('database.connections.mysql.database', ''),
                'username' => (string) config('database.connections.mysql.username', ''),
                'password' => (string) config('database.connections.mysql.password', ''),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function isInstalled(): bool
    {
        return $this->state->isInstalled();
    }

    /**
     * @inheritDoc
     */
    public function getStage(): InstallStage
    {
        return $this->state->getStage();
    }

    /**
     * @inheritDoc
     */
    public function testDatabase(DatabaseConfigDto $dto): void
    {
        $this->databaseTester->test($dto);
    }

    /**
     * @inheritDoc
     */
    public function saveDatabase(DatabaseConfigDto $dto): void
    {
        $this->databaseTester->test($dto);
        $this->envWriter->update($dto->toEnv());
        $this->databaseTester->migrate();
        $this->state->markStage(InstallStage::Database);
    }

    /**
     * @inheritDoc
     */
    public function saveAdmin(AdminDto $dto): void
    {
        $this->state->putPayload('admin', [
            'name' => $dto->name,
            'email' => $dto->email,
            'password_hash' => Hash::make($dto->password),
        ]);
        $this->state->markStage(InstallStage::Admin);
    }

    /**
     * @inheritDoc
     */
    public function saveFirstObject(FirstObjectDto $dto): array
    {
        $adminPayload = $this->state->getPayload('admin');

        if ($adminPayload === null) {
            throw new RuntimeException('Admin data missing — complete the admin step first.');
        }

        $admin = new AdminDto(
            name: $adminPayload['name'],
            email: $adminPayload['email'],
            password: $adminPayload['password_hash'],
        );

        return DB::transaction(function () use ($admin, $dto, $adminPayload) {
            $result = $this->provisioning->provision(
                object: $dto,
                admin: new AdminDto(
                    name: $admin->name,
                    email: $admin->email,
                    password: bin2hex(random_bytes(16)),
                ),
            );

            // Overwrite password with the already-hashed one from install payload.
            // DB::table bypasses the 'password' => 'hashed' cast on User — otherwise Eloquent
            // would hash the hash again (double-hash bug, login did not work).
            DB::table('sec_users')
                ->where('id', $result['user']->id)
                ->update(['password' => $adminPayload['password_hash']]);
            $result['user']->refresh();

            $this->state->putPayload('object', [
                'id' => $result['object']->id,
                'company_id' => $result['company']->id,
            ] + $dto->toArray());
            $this->state->putPayload('company', [
                'id' => $result['company']->id,
                'slug' => $result['company']->slug,
            ]);
            $this->state->markStage(InstallStage::Object);

            return [
                'company_id' => $result['company']->id,
                'user_id' => $result['user']->id,
                'object_id' => $result['object']->id,
            ];
        });
    }

    /**
     * @inheritDoc
     */
    public function saveFirstCamera(FirstCameraDto $dto): void
    {
        $objectPayload = $this->state->getPayload('object');

        if ($objectPayload === null || empty($objectPayload['id']) || empty($objectPayload['company_id'])) {
            throw new RuntimeException('Object data missing — complete the object step first.');
        }

        DB::transaction(function () use ($dto, $objectPayload) {
            $companyId = (int) $objectPayload['company_id'];
            $objectId = (int) $objectPayload['id'];

            // Bypass CameraService::create — slug uniqueness needs the company scope, but
            // there is no auth user during install so the global CompanyScope returns no filter.
            // Doing the slug check explicitly within $companyId keeps behaviour deterministic.
            $camera = Camera::create([
                'company_id' => $companyId,
                'object_id' => $objectId,
                'name' => $dto->name,
                'display_name' => $dto->displayName ?? $dto->name,
                'slug' => $this->uniqueCameraSlug($companyId, $dto->name),
                'address' => $dto->address,
                'ip' => $dto->ip,
                'stream_url' => $dto->streamUrl ?? '',
                'stream_login' => $dto->streamLogin,
                'stream_password_encrypted' => $dto->streamPassword !== null
                    ? Crypt::encryptString($dto->streamPassword)
                    : null,
                'is_online' => false,
            ]);

            // Mirror CameraService::bindFileManagerFolder — installer bypasses that service for
            // the slug-uniqueness reason above, but the camera still needs an FM path id, otherwise
            // AlbumSyncService::syncCamera() short-circuits and the day folders never get scanned.
            $object = VisionObject::withoutGlobalScopes()->find($objectId);
            if ($object !== null) {
                $objectFolder = $this->fileManager->findOrCreateDirectory($object->slug, $companyId, null, 'local');
                $cameraFolder = $this->fileManager->findOrCreateDirectory($camera->slug, $companyId, $objectFolder->id, 'local');
                $camera->forceFill(['file_manager_path_id' => $cameraFolder->id])->save();
            }

            $this->state->putPayload('camera', ['id' => $camera->id] + $dto->toArray());
            $this->state->markStage(InstallStage::Camera);
        });
    }

    /**
     * Generates a unique camera slug within the given company (installer bypasses auth-driven scopes).
     *
     * @param int $companyId Company scope.
     * @param string $name Source name.
     * @return string Unique slug within the company.
     */
    private function uniqueCameraSlug(int $companyId, string $name): string
    {
        $base = Str::slug($name) ?: 'camera';
        $slug = $base;
        $i = 2;

        while (Camera::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /**
     * @inheritDoc
     */
    public function finalize(): void
    {
        // APP_INSTALLED=true + config:clear + removal of temporary wizard payloads
        // is now handled by the repository itself — the service does not duplicate EnvWriter.
        $this->state->finalize();

        $this->ensurePassportClients();

        Event::dispatch(new InstallationCompleted());
    }

    /**
     * Ensures Passport has the password grant client required by LoginController
     * (reads its name from config/passport.php -> 'desktop'). Idempotent — re-running
     * finalize does not create duplicates.
     */
    private function ensurePassportClients(): void
    {
        $desktopName = (string) config('passport.clients.desktop', 'Desktop Password Grant Client');

        if (!PassportClient::where('name', $desktopName)->exists()) {
            // --public yields a password grant client WITHOUT a secret — AuthorizationService::requestToken
            // sends grant_type=password with just the client_id, so the client must be public,
            // otherwise /oauth/token returns 401.
            Artisan::call('passport:client', [
                '--password' => true,
                '--public' => true,
                '--no-interaction' => true,
                '--name' => $desktopName,
            ]);
        }

        if (!PassportClient::where('personal_access_client', true)->exists()) {
            Artisan::call('passport:client', [
                '--personal' => true,
                '--no-interaction' => true,
                '--name' => 'Vision Personal Access',
            ]);
        }
    }
}
