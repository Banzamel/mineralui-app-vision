<?php

namespace Albums\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms the Album model into JSON for the frontend.
 */
class AlbumResource extends JsonResource
{
    /**
     * @param Request $request Current request.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'camera_id' => $this->camera_id,
            'date' => optional($this->date)->format('Y-m-d'),
            'folder_name' => $this->folder_name,
            'photos_count' => (int) $this->photos_count,
            'file_manager_path_id' => $this->file_manager_path_id,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'photos' => PhotoResource::collection($this->whenLoaded('photos')),
        ];
    }
}
