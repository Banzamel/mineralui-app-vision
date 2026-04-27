<?php

namespace Installer\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource serializing the installer state into the API response.
 */
class InstallStatusResource extends JsonResource
{
    /**
     * Returns the installer state + optional database defaults that the frontend uses
     * to auto-fill the first step of the wizard (host/port/database/username).
     *
     * @param Request $request Current HTTP request.
     * @return array<string, mixed> Installer state.
     */
    public function toArray(Request $request): array
    {
        return [
            'installed' => (bool) ($this->resource['installed'] ?? false),
            'stage' => $this->resource['stage'] ?? 'fresh',
            'database_defaults' => $this->resource['database_defaults'] ?? null,
        ];
    }
}
