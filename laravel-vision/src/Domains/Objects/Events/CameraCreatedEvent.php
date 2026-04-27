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
 * Event after a new camera is created.
 * Also listened to by ProvisionCameraFolder (creates a directory in FileManager).
 */
class CameraCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param Camera $camera Newly created camera.
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
        return 'cameras.created';
    }

    /**
     * Payload — minimal camera data.
     *
     * @return array<string, mixed> Event data.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->camera->id,
            'name' => $this->camera->name,
            'object_id' => $this->camera->object_id,
        ];
    }
}
