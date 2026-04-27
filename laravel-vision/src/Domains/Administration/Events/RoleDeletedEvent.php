<?php

namespace Administration\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\Permission\Models\Role;

/**
 * Event dispatched after a role has been removed from the company.
 * Holds a snapshot of the data because the role may no longer exist when the event is broadcast.
 */
class RoleDeletedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private int $roleId;
    private string $roleName;
    private int $companyId;

    /**
     * Stores a snapshot of the deleted role's data and information about the actor.
     *
     * @param Role $role role that has been deleted
     * @param \Auth\Models\User $user actor that performed the operation (authenticated user)
     */
    public function __construct(
        Role $role,
        public readonly \Auth\Models\User $user
    ) {
        $this->roleId = $role->id;
        $this->roleName = $role->name;
        $this->companyId = $role->company_id;
    }

    /**
     * Returns the list of channels the event should be broadcast on (company presence channel).
     *
     * @return array<int, PresenceChannel> list of broadcast channels
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel(config('broadcasting.channel') . '.company.' . $this->companyId),
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
     * Returns the data attached to the broadcast event (which role, who removed it, when).
     *
     * @return array<string, mixed> event payload
     */
    public function broadcastWith(): array
    {
        return [
            'role' => ['id' => $this->roleId, 'name' => $this->roleName],
            'user' => ['name' => $this->user->name],
            'action' => 'deleted',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
