<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Service Provider that registers interface => implementation bindings for all domains.
 * Laravel reads the public $bindings property during container bootstrap.
 */
class RegisterServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string> Mapping of service and repository interfaces.
     */
    public array $bindings = [
        // Services
        \Auth\Services\Interfaces\AuthorizationServiceInterface::class => \Auth\Services\AuthorizationService::class,
        \Auth\Services\Interfaces\SocialAuthServiceInterface::class => \Auth\Services\SocialAuthService::class,
        \Administration\Services\Interfaces\RoleServiceInterface::class => \Administration\Services\RoleService::class,
        \Administration\Services\Interfaces\PermissionServiceInterface::class => \Administration\Services\PermissionService::class,
        \Administration\Services\Interfaces\UserManagementServiceInterface::class => \Administration\Services\UserManagementService::class,
        \Administration\Services\Interfaces\UserActivityServiceInterface::class => \Administration\Services\UserActivityService::class,
        \Administration\Services\Interfaces\UserSessionServiceInterface::class => \Administration\Services\UserSessionService::class,
        \FileManager\Services\Interfaces\FileManagerServiceInterface::class => \FileManager\Services\FileManagerService::class,
        \Installer\Services\Interfaces\InstallerServiceInterface::class => \Installer\Services\InstallerService::class,
        \Installer\Services\Interfaces\EnvWriterServiceInterface::class => \Installer\Services\EnvWriterService::class,
        \Installer\Services\Interfaces\DatabaseTesterServiceInterface::class => \Installer\Services\DatabaseTesterService::class,
        \Installer\Services\Interfaces\CompanyProvisioningServiceInterface::class => \Installer\Services\CompanyProvisioningService::class,

        // Objects domain
        \Objects\Services\Interfaces\VisionObjectServiceInterface::class => \Objects\Services\VisionObjectService::class,
        \Objects\Services\Interfaces\CameraServiceInterface::class => \Objects\Services\CameraService::class,
        \Objects\Services\Interfaces\UserScopeServiceInterface::class => \Objects\Services\UserScopeService::class,

        // Albums domain
        \Albums\Services\Interfaces\AlbumServiceInterface::class => \Albums\Services\AlbumService::class,
        \Albums\Services\Interfaces\AlbumSyncServiceInterface::class => \Albums\Services\AlbumSyncService::class,
        \Albums\Services\Interfaces\RetentionServiceInterface::class => \Albums\Services\RetentionService::class,
        \Albums\Services\Interfaces\PhotoStreamServiceInterface::class => \Albums\Services\PhotoStreamService::class,
        \Albums\Services\Interfaces\ThumbnailServiceInterface::class => \Albums\Services\ThumbnailService::class,
        \Push\Services\Interfaces\PushSubscriptionServiceInterface::class => \Push\Services\PushSubscriptionService::class,
        \Push\Services\Interfaces\WebPushSenderInterface::class => \Push\Services\WebPushSender::class,

        // Notifications / System
        \Notifications\Services\Interfaces\NotificationServiceInterface::class => \Notifications\Services\NotificationService::class,
        \System\Services\Interfaces\SystemStatusServiceInterface::class => \System\Services\SystemStatusService::class,

        // Repositories
        \Auth\Repositories\Interfaces\AuthLogRepositoryInterface::class => \Auth\Repositories\AuthLogRepository::class,
        \Auth\Repositories\Interfaces\TokenRepositoryInterface::class => \Auth\Repositories\TokenRepository::class,
        \Administration\Repositories\Interfaces\UserRepositoryInterface::class => \Administration\Repositories\UserRepository::class,
        \Administration\Repositories\Interfaces\RoleRepositoryInterface::class => \Administration\Repositories\RoleRepository::class,
        \FileManager\Repositories\Interfaces\FileManagerRepositoryInterface::class => \FileManager\Repositories\FileManagerRepository::class,
        \Installer\Repositories\Interfaces\InstallStateRepositoryInterface::class => \Installer\Repositories\InstallStateRepository::class,

        // Objects repositories
        \Objects\Repositories\Interfaces\VisionObjectRepositoryInterface::class => \Objects\Repositories\VisionObjectRepository::class,
        \Objects\Repositories\Interfaces\CameraRepositoryInterface::class => \Objects\Repositories\CameraRepository::class,
        \Objects\Repositories\Interfaces\UserScopeRepositoryInterface::class => \Objects\Repositories\UserScopeRepository::class,

        // Albums repositories
        \Albums\Repositories\Interfaces\AlbumRepositoryInterface::class => \Albums\Repositories\AlbumRepository::class,
        \Albums\Repositories\Interfaces\PhotoRepositoryInterface::class => \Albums\Repositories\PhotoRepository::class,
        \Push\Repositories\Interfaces\PushSubscriptionRepositoryInterface::class => \Push\Repositories\PushSubscriptionRepository::class,

        // Notifications repositories
        \Notifications\Repositories\Interfaces\NotificationRepositoryInterface::class => \Notifications\Repositories\NotificationRepository::class,
    ];
}
