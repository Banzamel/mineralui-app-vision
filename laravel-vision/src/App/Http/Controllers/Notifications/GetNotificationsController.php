<?php

namespace App\Http\Controllers\Notifications;

use Illuminate\Http\JsonResponse;
use Notifications\Requests\NotificationListRequest;
use Notifications\Resources\NotificationResource;
use Notifications\Services\Interfaces\NotificationServiceInterface;

/**
 * GET /notifications — list of notifications for the current user.
 */
readonly class GetNotificationsController
{
    /**
     * @param NotificationServiceInterface $service notifications service
     */
    public function __construct(private NotificationServiceInterface $service)
    {
    }

    /**
     * @param NotificationListRequest $request validated list query
     * @return JsonResponse list of notifications serialized by NotificationResource
     */
    public function __invoke(NotificationListRequest $request): JsonResponse
    {
        $notifications = $this->service->list($request->getDto());
        return NotificationResource::collection($notifications)->response();
    }
}
