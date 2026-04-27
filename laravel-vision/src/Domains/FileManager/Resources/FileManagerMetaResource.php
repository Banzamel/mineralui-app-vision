<?php

namespace FileManager\Resources;

use FileManager\Models\FileManagerMeta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource transforming a file metadata model into the JSON format returned to the client.
 *
 * @mixin FileManagerMeta
 */
class FileManagerMetaResource extends JsonResource
{
    /**
     * Transforms file metadata into an array ready to be returned in a JSON response.
     *
     * @param \Illuminate\Http\Request $request Current HTTP request.
     * @return array Array of metadata fields.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mime_type' => $this->mime_type,
            'extension' => $this->extension,
            'metadata' => $this->metadata,
            'checksum' => $this->checksum,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
