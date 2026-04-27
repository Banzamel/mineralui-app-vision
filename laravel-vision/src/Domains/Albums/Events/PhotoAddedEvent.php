<?php

namespace Albums\Events;

use Albums\Models\Photo;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired after a new photo is added to an album (by sync).
 */
class PhotoAddedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param Photo $photo Newly added photo.
     */
    public function __construct(public Photo $photo)
    {
    }

    /**
     * Broadcast channel — private company channel.
     *
     * @return array<int, Channel> List of channels to publish on.
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('vision.company.' . $this->photo->album->company_id)];
    }

    /**
     * Client-side event name (Echo).
     *
     * @return string Event name.
     */
    public function broadcastAs(): string
    {
        return 'albums.photo_added';
    }

    /**
     * Payload sent to the client (lightweight photo info).
     *
     * @return array<string, mixed> Event data.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->photo->id,
            'album_id' => $this->photo->album_id,
            'filename' => $this->photo->filename,
        ];
    }
}
