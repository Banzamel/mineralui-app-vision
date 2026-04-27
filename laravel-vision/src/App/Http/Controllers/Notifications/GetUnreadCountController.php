<?php

namespace App\Http\Controllers\Notifications;

use Illuminate\Http\JsonResponse;
use Notifications\Requests\NotificationUnreadCountRequest;
use Notifications\Services\Interfaces\NotificationServiceInterface;

/**
 * GET /notifications/unread-count — number of unread notifications for the current user.
 */
readonly class GetUnreadCountController
{
    /**
     * @param NotificationServiceInterface $service notifications service
     */
    public function __construct(private NotificationServiceInterface $service)
    {
    }

    /**
     * @param NotificationUnreadCountRequest $request validated unread-count query
     * @return JsonResponse { data: { count: int } }
     */
    public function __invoke(NotificationUnreadCountRequest $request): JsonResponse
    {
        $count = $this->service->unreadCount($request->getDto());
        return response()->json(['data' => ['count' => $count]]);
    }
}
