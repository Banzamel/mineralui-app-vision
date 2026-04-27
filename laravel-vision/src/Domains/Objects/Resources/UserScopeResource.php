<?php

namespace Objects\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Single visibility row for API output.
 */
class UserScopeResource extends JsonResource
{
    /**
     * @param Request $request Current request.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'scope_id' => $this->scope_id,
        ];
    }
}
