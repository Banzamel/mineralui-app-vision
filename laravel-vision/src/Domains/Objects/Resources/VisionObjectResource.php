<?php

namespace Objects\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a VisionObject model into a JSON chunk for the frontend.
 * Outputs flat fields plus relations, if they were eagerly loaded by Eloquent.
 */
class VisionObjectResource extends JsonResource
{
    /**
     * Maps model columns to a frontend-friendly structure.
     *
     * @param Request $request Current request (required by the base JsonResource).
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'address' => $this->address,
            'description' => $this->description,
            'main_photo_path' => $this->main_photo_path,
            'main_photo_url' => $this->main_photo_url,
            'depth' => $this->depth,
            'is_active' => (bool) $this->is_active,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'children' => self::collection($this->whenLoaded('children')),
            'cameras' => CameraResource::collection($this->whenLoaded('cameras')),
        ];
    }
}
