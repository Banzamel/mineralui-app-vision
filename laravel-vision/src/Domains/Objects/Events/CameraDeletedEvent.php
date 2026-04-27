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
 * Event after a camera is deleted.
 */
class CameraDeletedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param Camera $camera Deleted camera.
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
        return 'cameras.deleted';
    }

    /**
     * Payload — just the ID of the deleted camera.
     *
     * @return array<string, mixed> Event data.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->camera->id,
        ];
    }
}
