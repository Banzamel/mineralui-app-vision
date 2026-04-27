<?php

namespace App\Http\Controllers\Notifications;

use Illuminate\Http\JsonResponse;
use Notifications\Requests\NotificationMarkReadRequest;
use Notifications\Services\Interfaces\NotificationServiceInterface;

/**
 * PATCH /notifications/{id}/read — marks a single notification as read.
 */
readonly class MarkNotificationReadController
{
    /**
     * @param NotificationServiceInterface $service notifications service
     */
    public function __construct(private NotificationServiceInterface $service)
    {
    }

    /**
     * @param NotificationMarkReadRequest $request validated mark-read request
     * @return JsonResponse { marked: true }
     */
    public function __invoke(NotificationMarkReadRequest $request): JsonResponse
    {
        $this->service->markRead($request->getDto());
        return response()->json(['marked' => true]);
    }
}
