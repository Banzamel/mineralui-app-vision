<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
        then: function () {
            // Public passport endpoints pozostają poza /api, żeby Passport grant_type=password
            // mógł być wołany po short URL (/oauth/login zamiast /api/oauth/login).
            Route::middleware('api')->group(__DIR__ . '/../routes/oauth.php');

            // Installer endpointy idą pod /api/install/* — inaczej frontendowy React route /install
            // (kreator SPA) kolidowałby z Vite proxy i przeglądarka dostawałaby JSON zamiast HTML.
            Route::prefix('api')->middleware('api')->group(__DIR__ . '/../routes/install.php');
        },
    )
    ->withCommands([
        \Albums\Commands\VisionSyncAlbumsCommand::class,
        \Albums\Commands\VisionRetentionCommand::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->use([
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
        ]);

        $middleware->alias([
            'teams.permission' => \App\Http\Middleware\SetPermissionsTeamId::class,
            'company.active' => \Shared\Middlewares\CheckCompanyNotExpired::class,
            'install.gate' => \Shared\Middlewares\InstallGate::class,
            'scope' => \Laravel\Passport\Http\Middleware\CheckTokenForAnyScope::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle authentication exceptions with a JSON response
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $exception, \Illuminate\Http\Request $request) {
            return response()->json([
                'message' => 'Unauthenticated',
                'exception' => $exception->getMessage(),
            ], 401);
        });

        // Handle authorization exceptions with a JSON response
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $exception, \Illuminate\Http\Request $request) {
            return response()->json([
                'message' => 'This action is unauthorized',
                'exception' => $exception->getMessage(),
            ], 403);
        });

        // Handle 404 errors with a JSON response
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $exception, \Illuminate\Http\Request $request) {
            return response()->json([
                'message' => 'The requested resource was not found',
                'exception' => $exception->getMessage(),
            ], 404);
        });

        // Handle validation exceptions with a JSON response
        $exceptions->render(function (\Illuminate\Validation\ValidationException $exception, \Illuminate\Http\Request $request) {
            return response()->json([
                'message' => 'The given data was invalid',
                'errors' => $exception->errors(),
            ], 422);
        });

    })->create();
