<?php

namespace App\Http\Controllers\Push;

use Illuminate\Http\JsonResponse;
use Push\Requests\SavePushSubscriptionRequest;
use Push\Services\Interfaces\PushSubscriptionServiceInterface;

/**
 * POST /vision/push/subscriptions — stores a web push subscription for the current user.
 */
readonly class SavePushSubscriptionController
{
    /**
     * @param PushSubscriptionServiceInterface $service subscriptions service
     */
    public function __construct(private PushSubscriptionServiceInterface $service)
    {
    }

    /**
     * @param SavePushSubscriptionRequest $request validated subscription payload
     * @return JsonResponse { id: int } (status 201)
     */
    public function __invoke(SavePushSubscriptionRequest $request): JsonResponse
    {
        $subscription = $this->service->save($request->user()->id, $request->getDto());
        return response()->json(['id' => $subscription->id], 201);
    }
}
