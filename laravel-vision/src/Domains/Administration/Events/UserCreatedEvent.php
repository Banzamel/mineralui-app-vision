<?php

namespace Administration\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched after a new user has been created in the company.
 * Broadcast on the company presence channel so that other users get a real-time notification.
 */
class UserCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Stores the data of the created user and the actor who created them.
     *
     * @param \Administration\Models\User $createdUser newly created user
     * @param \Auth\Models\User $user actor that performed the operation (authenticated user)
     */
    public function __construct(
        public readonly \Administration\Models\User $createdUser,
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
            new PresenceChannel(config('broadcasting.channel') . '.company.' . $this->createdUser->company_id),
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
            'target_user' => ['id' => $this->createdUser->id, 'name' => $this->createdUser->name, 'email' => $this->createdUser->email],
            'user' => ['name' => $this->user->name],
            'action' => 'created',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
