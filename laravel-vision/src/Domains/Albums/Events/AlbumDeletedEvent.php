<?php

namespace Albums\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event broadcast after an album is deleted (by a user action or the retention job).
 */
class AlbumDeletedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param int $albumId Id of the deleted album.
     * @param int $companyId Owning company id.
     * @param int $cameraId Source camera id.
     */
    public function __construct(
        public int $albumId,
        public int $companyId,
        public int $cameraId,
    ) {
    }

    /**
     * Private company channel.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('vision.company.' . $this->companyId)];
    }

    /**
     * Frontend event name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'albums.deleted';
    }

    /**
     * Payload — album id and camera id are enough for the frontend to drop it from the list.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->albumId,
            'camera_id' => $this->cameraId,
        ];
    }
}
