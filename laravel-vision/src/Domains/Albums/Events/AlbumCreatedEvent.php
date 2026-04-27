<?php

namespace Albums\Events;

use Albums\Models\Album;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired after a new album is created (e.g. when sync discovers a new day).
 */
class AlbumCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param Album $album Newly created album.
     */
    public function __construct(public Album $album)
    {
    }

    /**
     * Broadcast channel — private company channel.
     *
     * @return array<int, Channel> List of channels to publish on.
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('vision.company.' . $this->album->company_id)];
    }

    /**
     * Client-side event name (Echo).
     *
     * @return string Event name.
     */
    public function broadcastAs(): string
    {
        return 'albums.created';
    }

    /**
     * Payload sent to the client (lightweight album info).
     *
     * @return array<string, mixed> Event data.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->album->id,
            'camera_id' => $this->album->camera_id,
            'date' => optional($this->album->date)->format('Y-m-d'),
        ];
    }
}
