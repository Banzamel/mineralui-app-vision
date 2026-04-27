<?php

namespace Administration\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched after a user's data has been updated.
 * Broadcast on the company presence channel so that other users see the change in real time.
 */
class UserUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Stores the data of the updated user and the actor who performed the edit.
     *
     * @param \Administration\Models\User $updatedUser user after the update
     * @param \Auth\Models\User $user actor that performed the operation (authenticated user)
     */
    public function __construct(
        public readonly \Administration\Models\User $updatedUser,
        public readonly \Auth\Models\User $user
    ) {}

    /**
     * Returns the list of channels the event should be broadcast on (company presence channel).
     *
     * @return array<int, PresenceChannel> list of broadcast channels
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel(config('broadcasting.channel') . '.company.' . $this->updatedUser->company_id),
        ];
    }

    /**
     * Returns the event name visible on the client side (frontend).
     *
     * @return string broadcast event name
     */
    public function broadcastAs(): string
    {
        return 'administration.user';
    }

    /**
     * Returns the data attached to the broadcast event (who, target, action, when).
     *
     * @return array<string, mixed> event payload
     */
    public function broadcastWith(): array
    {
        return [
            'target_user' => ['id' => $this->updatedUser->id, 'name' => $this->updatedUser->name, 'email' => $this->updatedUser->email],
            'user' => ['name' => $this->user->name],
            'action' => 'updated',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
