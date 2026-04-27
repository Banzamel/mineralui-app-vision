<?php

namespace Notifications\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Notifications\Models\Notification;

/**
 * Event broadcast after a new notification is created — frontend uses it to bump the unread counter.
 */
class NotificationCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param Notification $notification Freshly created notification.
     */
    public function __construct(public Notification $notification)
    {
    }

    /**
     * Private user channel.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('vision.user.' . $this->notification->user_id)];
    }

    /**
     * Frontend event name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'notifications.created';
    }

    /**
     * Payload — full notification in the shape expected by the frontend type.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => (string) $this->notification->id,
            'type' => $this->notification->type,
            'severity' => $this->notification->severity,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            // Structured payload for frontend i18n — frontend prefers this when present,
            // falls back to title/message (EN) when type is unknown.
            'data' => $this->notification->data,
            'link' => $this->notification->link,
            'read' => $this->notification->read_at !== null,
            'created_at' => optional($this->notification->created_at)->toIso8601String(),
        ];
    }
}
