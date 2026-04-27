<?php

namespace App\Http\Controllers\Notifications;

use Illuminate\Http\JsonResponse;
use Notifications\Requests\NotificationDeleteRequest;
use Notifications\Services\Interfaces\NotificationServiceInterface;

/**
 * DELETE /notifications/{id} — deletes a single notification owned by the current user.
 */
readonly class DeleteNotificationController
{
    /**
     * @param NotificationServiceInterface $service notifications service
     */
    public function __construct(private NotificationServiceInterface $service)
    {
    }

    /**
     * @param NotificationDeleteRequest $request validated delete request
     * @return JsonResponse { deleted: true }
     */
    public function __invoke(NotificationDeleteRequest $request): JsonResponse
    {
        $this->service->delete($request->getDto());
        return response()->json(['deleted' => true]);
    }
}
