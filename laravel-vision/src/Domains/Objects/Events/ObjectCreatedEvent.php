<?php

namespace Objects\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Objects\Models\VisionObject;

/**
 * Event broadcast after a new object is created in the tree.
 * Listened to by the frontend (Reverb) to refresh the list.
 */
class ObjectCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param VisionObject $object Object that was created.
     */
    public function __construct(public VisionObject $object)
    {
    }

    /**
     * Private company channel — where object notifications go.
     *
     * @return array<int, Channel> Channels to broadcast on.
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('vision.company.' . $this->object->company_id)];
    }

    /**
     * Event name on the frontend side.
     *
     * @return string Event name.
     */
    public function broadcastAs(): string
    {
        return 'objects.created';
    }

    /**
     * Payload sent over the socket.
     *
     * @return array<string, mixed> Event data.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->object->id,
            'name' => $this->object->name,
            'parent_id' => $this->object->parent_id,
        ];
    }
}
