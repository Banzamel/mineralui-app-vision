<?php

namespace Tests\Unit\Services\Installer;

use Illuminate\Support\Facades\Event;
use Installer\Dtos\AdminDto;
use Installer\Dtos\FirstObjectDto;
use Installer\Enums\InstallStage;
use Installer\Events\InstallationCompleted;
use Installer\Repositories\Interfaces\InstallStateRepositoryInterface;
use Installer\Services\InstallerService;
use Installer\Services\Interfaces\CompanyProvisioningServiceInterface;
use Installer\Services\Interfaces\DatabaseTesterServiceInterface;
use Installer\Services\Interfaces\EnvWriterServiceInterface;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class InstallerServiceTest extends TestCase
{
    private InstallStateRepositoryInterface $state;
    private DatabaseTesterServiceInterface $dbTester;
    private EnvWriterServiceInterface $envWriter;
    private CompanyProvisioningServiceInterface $provisioning;
    private InstallerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->state = Mockery::mock(InstallStateRepositoryInterface::class);
        $this->dbTester = Mockery::mock(DatabaseTesterServiceInterface::class);
        $this->envWriter = Mockery::mock(EnvWriterServiceInterface::class);
        $this->provisioning = Mockery::mock(CompanyProvisioningServiceInterface::class);

        $this->service = new InstallerService(
            $this->state,
            $this->dbTester,
            $this->envWriter,
            $this->provisioning,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_is_installed_delegates_to_state_repository(): void
    {
        $this->state->shouldReceive('isInstalled')->once()->andReturn(true);

        $this->assertTrue($this->service->isInstalled());
    }

    public function test_get_stage_delegates_to_state_repository(): void
    {
        $this->state->shouldReceive('getStage')->once()->andReturn(InstallStage::Admin);

        $this->assertSame(InstallStage::Admin, $this->service->getStage());
    }

    public function test_save_admin_persists_hashed_password_and_advances_stage(): void
    {
        $this->state->shouldReceive('putPayload')->once()->with('admin', Mockery::on(function (array $payload) {
            // password_hash is bcrypt-format; never round-tripped as plain text.
            return $payload['name'] === 'Admin'
                && $payload['email'] === 'admin@vision.local'
                && isset($payload['password_hash'])
                && str_starts_with($payload['password_hash'], '$2y$');
        }));
        $this->state->shouldReceive('markStage')->once()->with(InstallStage::Admin);

        $this->service->saveAdmin(new AdminDto('Admin', 'admin@vision.local', 'secret123'));

        $this->assertTrue(true); // Mockery expectations cover the work; explicit assert for PHPUnit.
    }

    public function test_save_first_object_throws_when_admin_payload_missing(): void
    {
        $this->state->shouldReceive('getPayload')->once()->with('admin')->andReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Admin data missing');

        $this->service->saveFirstObject(new FirstObjectDto(
            name: 'Building 1',
            type: 'building',
            address: 'ul. Test 1',
            description: null,
        ));
    }

    public function test_finalize_invokes_state_and_dispatches_completion_event(): void
    {
        Event::fake([InstallationCompleted::class]);
        $this->state->shouldReceive('finalize')->once();

        $this->service->finalize();

        Event::assertDispatched(InstallationCompleted::class);
    }
}
