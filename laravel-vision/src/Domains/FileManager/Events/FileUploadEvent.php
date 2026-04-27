<?php

namespace FileManager\Events;

use FileManager\Models\FileManagerPath;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched after a file upload - broadcasts the information to other users in the company.
 */
class FileUploadEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Stores who uploaded the file and which file was uploaded.
     *
     * @param \Auth\Models\User $user User who uploaded the file.
     * @param \FileManager\Models\FileManagerPath $filePath Database entry describing the uploaded file.
     */
    public function __construct(
        public readonly \Auth\Models\User $user,
        public readonly FileManagerPath $filePath,
    ) {}

    /**
     * Indicates the broadcast channel - shared across the user's whole company.
     *
     * @return array List of channels the event will be dispatched on.
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel(config('broadcasting.channel') . '.company' . '.' . $this->user->company_id),
        ];
    }

    /**
     * Returns the broadcast event name on the client side.
     *
     * @return string Event name visible to the frontend.
     */
    public function broadcastAs(): string
    {
        return 'file.uploaded';
    }

    /**
     * Builds the data that will be sent along with the event to clients.
     *
     * @return array Array with file and user information.
     */
    public function broadcastWith(): array
    {
        return [
            'user' => [
                'name' => $this->user->name,
            ],
            'file' => [
                'id' => $this->filePath->id,
                'name' => $this->filePath->name,
                'type' => $this->filePath->type->value,
            ],
            'message' => 'file.uploaded',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
