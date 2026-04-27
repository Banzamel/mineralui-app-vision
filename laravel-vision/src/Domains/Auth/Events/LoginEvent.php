<?php

namespace Auth\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched after a user logs in, broadcast on the company channel.
 */
class LoginEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Creates a new user login event.
     *
     * @param \Auth\Models\User $user The user who just logged in
     */
    public function __construct(
        public readonly \Auth\Models\User $user
    ) {}

    /**
     * Returns the broadcast channels (presence channel for the user's company).
     *
     * @return array<int, PresenceChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel(config('broadcasting.channel') . '.company.' . $this->user->company_id),
        ];
    }

    /**
     * Returns the event name used for broadcasting.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'auth.user';
    }

    /**
     * Returns the data sent along with the broadcast event.
     *
     * @return array<string, mixed> User data, action and event timestamp
     */
    public function broadcastWith(): array
    {
        return [
            'user' => ['name' => $this->user->name],
            'action' => 'login',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
