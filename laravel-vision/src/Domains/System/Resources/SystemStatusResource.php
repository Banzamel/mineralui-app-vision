<?php

namespace System\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use System\Dtos\SystemStatusDto;

/**
 * JSON representation of the system status payload — shape kept 1:1 with the frontend contract.
 *
 * @mixin SystemStatusDto
 */
class SystemStatusResource extends JsonResource
{
    /**
     * @inheritDoc
     */
    public function toArray(Request $request): array
    {
        /** @var SystemStatusDto $status */
        $status = $this->resource;
        $disk = $status->getDisk();

        return [
            'disk' => [
                'used_bytes' => $disk->getUsedBytes(),
                'total_bytes' => $disk->getTotalBytes(),
                'percent' => $disk->getPercent(),
            ],
            'version' => $status->getVersion(),
        ];
    }
}
