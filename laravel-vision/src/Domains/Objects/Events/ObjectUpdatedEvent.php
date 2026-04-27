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
 * Event broadcast after editing an object in the tree.
 */
class ObjectUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param VisionObject $object Updated object.
     */
    public function __construct(public VisionObject $object)
    {
    }

    /**
     * Private company channel.
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
        return 'objects.updated';
    }

    /**
     * Payload — minimal data of the updated object.
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
