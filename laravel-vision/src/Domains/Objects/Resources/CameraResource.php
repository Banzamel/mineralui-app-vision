<?php

namespace Objects\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Camera model into JSON for the frontend.
 * The encrypted password is NEVER returned — the server proxies the stream itself.
 */
class CameraResource extends JsonResource
{
    /**
     * Maps camera columns to a safe client-facing view.
     *
     * @param Request $request Current request.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'object_id' => $this->object_id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'slug' => $this->slug,
            'address' => $this->address,
            'ip' => $this->ip,
            'stream_url' => $this->stream_url,
            'stream_login' => $this->stream_login,
            'main_photo_path' => $this->main_photo_path,
            'main_photo_url' => $this->main_photo_url,
            'file_manager_path_id' => $this->file_manager_path_id,
            'is_online' => (bool) $this->is_online,
            'is_active' => (bool) $this->is_active,
            'motion_preview_enabled' => (bool) $this->motion_preview_enabled,
            'last_seen_at' => optional($this->last_seen_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
