<?php

namespace Objects\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Objects\Models\Camera;

/**
 * Event after editing an existing camera.
 */
class CameraUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param Camera $camera Updated camera.
     */
    public function __construct(public Camera $camera)
    {
    }

    /**
     * Private company channel.
     *
     * @return array<int, Channel> Channels to broadcast on.
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('vision.company.' . $this->camera->company_id)];
    }

    /**
     * Event name on the frontend side.
     *
     * @return string Event name.
     */
    public function broadcastAs(): string
    {
        return 'cameras.updated';
    }

    /**
     * Payload — minimal camera data along with the online status.
     *
     * @return array<string, mixed> Event data.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->camera->id,
            'name' => $this->camera->name,
            'object_id' => $this->camera->object_id,
            'is_online' => (bool) $this->camera->is_online,
        ];
    }
}
