<?php

namespace App\Events;

use App\Models\ChatGroup;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupAdded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ChatGroup $group;

    public int $targetUserId;

    /**
     * Create a new event instance.
     */
    public function __construct(ChatGroup $group, int $targetUserId)
    {
        $this->group = $group->load(['members', 'creator']);
        $this->targetUserId = $targetUserId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.'.$this->targetUserId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'group' => $this->group,
        ];
    }
}
