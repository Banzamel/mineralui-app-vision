<?php

namespace Notifications\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JSON representation of a notification matching the contract expected by the frontend.
 *
 * @property int $id
 * @property string $type
 * @property string $severity
 * @property string $title
 * @property string $message
 * @property array<string, mixed>|null $data
 * @property string|null $link
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class NotificationResource extends JsonResource
{
    /**
     * @inheritDoc
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'type' => $this->type,
            'severity' => $this->severity,
            'title' => $this->title,
            'message' => $this->message,
            // Structured payload — frontend prefers this over title/message and renders
            // via i18n key `notifications.<type>` interpolated with the data.
            'data' => $this->data,
            'link' => $this->link,
            'read' => $this->read_at !== null,
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
