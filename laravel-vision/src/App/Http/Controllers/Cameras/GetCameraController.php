<?php

namespace App\Http\Controllers\Cameras;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Objects\Resources\CameraResource;
use Objects\Services\Interfaces\CameraServiceInterface;

/**
 * GET /vision/cameras/{id} — details of a single camera (respecting visibility scope).
 */
readonly class GetCameraController
{
    /**
     * @param CameraServiceInterface $service Camera service.
     */
    public function __construct(private CameraServiceInterface $service)
    {
    }

    /**
     * @param Request $request Request (used for policy check).
     * @param int $id Camera ID.
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function __invoke(Request $request, int $id): JsonResponse
    {
        $camera = $this->service->find($id);

        if (! $request->user()->can('view', $camera)) {
            throw new AuthorizationException('Nie masz tej kamery w swoim zasięgu.');
        }

        return (new CameraResource($camera))->response();
    }
}
