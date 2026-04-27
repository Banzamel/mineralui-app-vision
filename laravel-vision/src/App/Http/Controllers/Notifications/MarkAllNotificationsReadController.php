<?php

namespace App\Http\Controllers\Notifications;

use Illuminate\Http\JsonResponse;
use Notifications\Requests\NotificationMarkAllReadRequest;
use Notifications\Services\Interfaces\NotificationServiceInterface;

/**
 * POST /notifications/read-all — marks all unread notifications of the current user as read.
 */
readonly class MarkAllNotificationsReadController
{
    /**
     * @param NotificationServiceInterface $service notifications service
     */
    public function __construct(private NotificationServiceInterface $service)
    {
    }

    /**
     * @param NotificationMarkAllReadRequest $request validated mark-all-read request
     * @return JsonResponse { marked: int }
     */
    public function __invoke(NotificationMarkAllReadRequest $request): JsonResponse
    {
        $count = $this->service->markAllRead($request->getDto());
        return response()->json(['marked' => $count]);
    }
}
