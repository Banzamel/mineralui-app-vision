<?php

namespace FileManager\Resources;

use FileManager\Models\FileManagerPath;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource transforming a file or directory path model into the JSON format returned to the client.
 *
 * @mixin FileManagerPath
 */
class FileManagerPathResource extends JsonResource
{
    /**
     * Transforms a file path entry into an array ready to be returned in a JSON response.
     *
     * @param \Illuminate\Http\Request $request Current HTTP request.
     * @return array Array of fields of the file or directory entry.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hash' => $this->hash,
            'parent_id' => $this->parent_id,
            'type' => $this->type->value,
            'storage' => $this->storage->value,
            'name' => $this->name,
            'size' => $this->size,
            'meta' => new FileManagerMetaResource($this->whenLoaded('meta')),
            'children' => FileManagerPathResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
