<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Protected routes
Broadcast::routes(['middleware' => ['auth:api']]);

Route::middleware(['auth:api', 'scope:api', 'teams.permission'])->group(function () {

    Route::middleware(['company.active'])->group(function () {

        // Self management
        Route::prefix('manage')->group(function () {
            Route::get('/me', \App\Http\Controllers\Manage\GetMeController::class);
            Route::put('/me', \App\Http\Controllers\Manage\UpdateMeController::class);
        });

        // Administration
        Route::prefix('administration')->group(function () {
            // Users
            Route::middleware('permission:users.view')->group(function () {
                Route::get('/users', \App\Http\Controllers\Users\GetUsersController::class)->name('users.index');
                Route::get('/users/{user}', \App\Http\Controllers\Users\GetUserController::class)->name('users.show');
                Route::get('/users/{user}/auth-logs', \App\Http\Controllers\Users\GetUserAuthLogsController::class)->name('users.logs');
            });
            Route::post('/users', \App\Http\Controllers\Users\CreateUserController::class)->middleware('permission:users.create')->name('users.store');
            Route::put('/users/{user}', \App\Http\Controllers\Users\UpdateUserController::class)->middleware('permission:users.update')->name('users.update');
            Route::patch('/users/{user}/active', \App\Http\Controllers\Users\SetUserActiveController::class)->middleware('permission:users.update')->name('users.active');
            Route::post('/users/{user}/avatar', \App\Http\Controllers\Users\UpdateUserAvatarController::class)->middleware('permission:users.update')->name('users.avatar');
            Route::post('/users/{user}/reset-password', \App\Http\Controllers\Users\ResetUserPasswordController::class)->middleware('permission:users.update')->name('users.reset-password');
            Route::delete('/users/{user}', \App\Http\Controllers\Users\DeleteUserController::class)->middleware('permission:users.delete')->name('users.destroy');

            // User sessions and activity (admin panel)
            Route::middleware('permission:users.view')->group(function () {
                Route::get('/auth-logs', \App\Http\Controllers\Users\GetAuthLogsSummaryController::class)->name('users.auth-logs.summary');
                Route::get('/user-activity', \App\Http\Controllers\Users\GetUserActivityController::class)->name('users.activity.index');
                Route::get('/user-sessions', \App\Http\Controllers\Users\GetUserSessionsController::class)->name('users.sessions.index');
            });
            Route::delete('/users/{user}/sessions/{session}', \App\Http\Controllers\Users\RevokeUserSessionController::class)->middleware('permission:users.update')->name('users.sessions.destroy');

            // User access scopes against the object tree (Building/Address/Camera)
            Route::middleware('permission:scopes.manage')->group(function () {
                Route::get('/users/{userId}/scopes', \App\Http\Controllers\UserScopes\GetUserScopesController::class)->name('users.scopes.index');
                Route::put('/users/{userId}/scopes', \App\Http\Controllers\UserScopes\UpdateUserScopesController::class)->name('users.scopes.update');
            });

            // Permissions
            Route::get('/permissions', \App\Http\Controllers\Permissions\GetPermissionsController::class)
                ->middleware('permission:permissions.view')
                ->name('permissions.index');

            // Roles
            Route::get('/roles', \App\Http\Controllers\Roles\GetRolesController::class)->middleware('permission:roles.view')->name('roles.index');
            Route::post('/roles', \App\Http\Controllers\Roles\CreateRoleController::class)->middleware('permission:roles.manage')->name('roles.store');
            Route::put('/roles/{role}', \App\Http\Controllers\Roles\UpdateRoleController::class)->middleware('permission:roles.manage')->name('roles.update');
            Route::delete('/roles/{role}', \App\Http\Controllers\Roles\DeleteRoleController::class)->middleware('permission:roles.manage')->name('roles.destroy');
        });

        // File Manager
        Route::prefix('files')->group(function () {
            Route::middleware('permission:files.view')->group(function () {
                Route::get('/', \App\Http\Controllers\FileManager\ListFilesController::class)->name('files.index');
                Route::get('/{pathId}', \App\Http\Controllers\FileManager\GetFileController::class)->name('files.show');
                Route::get('/{pathId}/download', \App\Http\Controllers\FileManager\DownloadFileController::class)->name('files.download');
            });
            Route::post('/directory', \App\Http\Controllers\FileManager\CreateDirectoryController::class)->middleware('permission:files.create')->name('files.directory.store');
            Route::post('/upload', \App\Http\Controllers\FileManager\UploadFileController::class)->middleware('permission:files.create')->name('files.upload');
            Route::put('/{pathId}', \App\Http\Controllers\FileManager\UpdateItemController::class)->middleware('permission:files.update')->name('files.update');
            Route::delete('/{pathId}', \App\Http\Controllers\FileManager\DeleteItemController::class)->middleware('permission:files.delete')->name('files.destroy');
        });

        // Vision — drzewo obiektów, kamery, albumy, zdjęcia, web push
        Route::prefix('vision')->group(function () {
            // Obiekty w drzewie
            Route::middleware('permission:objects.view')->group(function () {
                Route::get('/objects/tree', \App\Http\Controllers\Objects\GetObjectsTreeController::class)->name('vision.objects.tree');
                Route::get('/objects', \App\Http\Controllers\Objects\GetObjectsListController::class)->name('vision.objects.index');
                Route::get('/objects/{id}', \App\Http\Controllers\Objects\GetObjectController::class)->name('vision.objects.show');
            });
            Route::post('/objects', \App\Http\Controllers\Objects\CreateObjectController::class)->middleware('permission:objects.manage')->name('vision.objects.store');
            Route::patch('/objects/{id}', \App\Http\Controllers\Objects\UpdateObjectController::class)->middleware('permission:objects.manage')->name('vision.objects.update');
            Route::post('/objects/{id}/main-photo', \App\Http\Controllers\Objects\UpdateObjectMainPhotoController::class)->middleware('permission:objects.manage')->name('vision.objects.main-photo');
            Route::delete('/objects/{id}', \App\Http\Controllers\Objects\DeleteObjectController::class)->middleware('permission:objects.manage')->name('vision.objects.destroy');

            // Kamery
            Route::middleware('permission:cameras.view')->group(function () {
                Route::get('/cameras', \App\Http\Controllers\Cameras\GetCamerasListController::class)->name('vision.cameras.index');
                Route::get('/cameras/{id}', \App\Http\Controllers\Cameras\GetCameraController::class)->name('vision.cameras.show');
                Route::get('/buildings', \App\Http\Controllers\Cameras\GetBuildingsController::class)->name('vision.buildings.index');
            });
            Route::post('/cameras', \App\Http\Controllers\Cameras\CreateCameraController::class)->middleware('permission:cameras.manage')->name('vision.cameras.store');
            Route::patch('/cameras/{id}', \App\Http\Controllers\Cameras\UpdateCameraController::class)->middleware('permission:cameras.manage')->name('vision.cameras.update');
            Route::post('/cameras/{id}/main-photo', \App\Http\Controllers\Cameras\UpdateCameraMainPhotoController::class)->middleware('permission:cameras.manage')->name('vision.cameras.main-photo');
            Route::delete('/cameras/{id}', \App\Http\Controllers\Cameras\DeleteCameraController::class)->middleware('permission:cameras.manage')->name('vision.cameras.destroy');

            // Albumy i zdjęcia
            Route::middleware('permission:albums.view')->group(function () {
                Route::get('/albums', \App\Http\Controllers\Albums\GetAlbumsListController::class)->name('vision.albums.index');
                Route::get('/albums/{id}', \App\Http\Controllers\Albums\GetAlbumController::class)->name('vision.albums.show');
                Route::get('/albums/{id}/photos', \App\Http\Controllers\Albums\GetAlbumPhotosController::class)->name('vision.albums.photos');
            });
            Route::delete('/albums/{id}', \App\Http\Controllers\Albums\DeleteAlbumController::class)->middleware('permission:albums.delete')->name('vision.albums.destroy');

            // Web push
            Route::post('/push/subscriptions', \App\Http\Controllers\Push\SavePushSubscriptionController::class)->name('vision.push.subscriptions.store');
        });

        // System status (photo storage usage + application version)
        Route::prefix('system')->group(function () {
            Route::get('/status', \App\Http\Controllers\System\GetSystemStatusController::class)->name('system.status');
        });

        // Per-user notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', \App\Http\Controllers\Notifications\GetNotificationsController::class)->name('notifications.index');
            Route::get('/unread-count', \App\Http\Controllers\Notifications\GetUnreadCountController::class)->name('notifications.unread-count');
            Route::patch('/{id}/read', \App\Http\Controllers\Notifications\MarkNotificationReadController::class)->name('notifications.read');
            Route::post('/read-all', \App\Http\Controllers\Notifications\MarkAllNotificationsReadController::class)->name('notifications.read-all');
            Route::delete('/{id}', \App\Http\Controllers\Notifications\DeleteNotificationController::class)->name('notifications.destroy');
            Route::delete('/', \App\Http\Controllers\Notifications\DeleteAllNotificationsController::class)->name('notifications.destroy-all');
        });

        // Current user activity
        Route::get('/my-activity', \App\Http\Controllers\Activity\GetMyActivityController::class)->name('my-activity');

    });
});

// Photo streaming uses Laravel's signed URLs instead of `auth:api` because <img> tags
// cannot send a Bearer token. The PhotoResource issues short-lived signed URLs that
// the browser can load directly; tampering with `id` invalidates the HMAC signature.
// `signed:relative` matches the relative URLs produced by URL::temporarySignedRoute(..., absolute=false).
Route::middleware('signed:relative')->group(function () {
    Route::get('/vision/photos/{id}/stream', \App\Http\Controllers\Albums\GetPhotoStreamController::class)
        ->name('vision.photos.stream');
    Route::get('/vision/photos/{id}/thumb', \App\Http\Controllers\Albums\GetPhotoThumbnailController::class)
        ->name('vision.photos.thumb');
});
