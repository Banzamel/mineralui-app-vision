<?php

namespace Albums\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

/**
 * Transforms the Photo model into JSON for the frontend.
 * The file URL is served through a streaming endpoint — it is not a direct link to storage.
 */
class PhotoResource extends JsonResource
{
    /**
     * @param Request $request Current request.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'album_id' => $this->album_id,
            'filename' => $this->filename,
            'width' => (int) $this->width,
            'height' => (int) $this->height,
            'bytes' => (int) $this->bytes,
            'mime' => $this->mime,
            'taken_at' => optional($this->taken_at)->toIso8601String(),
            // Short-lived signed URLs — both stream (full resolution) and thumb (400x300 JPEG)
            // routes are gated by `signed` middleware (not `auth:api`), so <img src> can load
            // them directly. Relative path keeps the proxied Host out of the URL.
            'stream_url' => URL::temporarySignedRoute(
                'vision.photos.stream',
                now()->addHours(2),
                ['id' => $this->id],
                false,
            ),
            'thumbnail_url' => URL::temporarySignedRoute(
                'vision.photos.thumb',
                now()->addHours(2),
                ['id' => $this->id],
                false,
            ),
        ];
    }
}
