<?php

namespace Administration\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\Permission\Models\Role;

/**
 * Event dispatched after a new role has been created in the company.
 * Broadcast on the company presence channel so other users see the new role live.
 */
class RoleCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Stores the data of the created role and the actor who created it.
     *
     * @param Role $role newly created role
     * @param \Auth\Models\User $user actor that performed the operation (authenticated user)
     */
    public function __construct(
        public readonly Role $role,
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
            new PresenceChannel(config('broadcasting.channel') . '.company.' . $this->role->company_id),
        ];
    }

    /**
     * Returns the event name visible on the client side (frontend).
     *
     * @return string broadcast event name
     */
    public function broadcastAs(): string
    {
        return 'administration.role';
    }

    /**
     * Returns the data attached to the broadcast event (which role, who created it, when).
     *
     * @return array<string, mixed> event payload
     */
    public function broadcastWith(): array
    {
        return [
            'role' => ['id' => $this->role->id, 'name' => $this->role->name],
            'user' => ['name' => $this->user->name],
            'action' => 'created',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
