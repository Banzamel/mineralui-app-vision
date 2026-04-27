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
 * Event broadcast after an object is deleted (soft delete).
 */
class ObjectDeletedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param VisionObject $object Deleted object.
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
        return 'objects.deleted';
    }

    /**
     * Payload — just the ID of the deleted object.
     *
     * @return array<string, mixed> Event data.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->object->id,
        ];
    }
}
