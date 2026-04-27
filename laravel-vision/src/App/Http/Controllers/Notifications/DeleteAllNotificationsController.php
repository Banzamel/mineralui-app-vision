<?php

namespace App\Http\Controllers\Notifications;

use Illuminate\Http\JsonResponse;
use Notifications\Requests\NotificationDeleteAllRequest;
use Notifications\Services\Interfaces\NotificationServiceInterface;

/**
 * DELETE /notifications — deletes all notifications of the current user.
 */
readonly class DeleteAllNotificationsController
{
    /**
     * @param NotificationServiceInterface $service notifications service
     */
    public function __construct(private NotificationServiceInterface $service)
    {
    }

    /**
     * @param NotificationDeleteAllRequest $request validated delete-all request
     * @return JsonResponse { deleted: int }
     */
    public function __invoke(NotificationDeleteAllRequest $request): JsonResponse
    {
        $count = $this->service->deleteAll($request->getDto());
        return response()->json(['deleted' => $count]);
    }
}
