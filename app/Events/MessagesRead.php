<?php

namespace App\Events;

use App\Models\ChatGroup;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagesRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chatGroup;
    public $user;
    public $messageIds;

    /**
     * Create a new event instance.
     */
    public function __construct(ChatGroup $chatGroup, User $user, array $messageIds)
    {
        $this->chatGroup = $chatGroup;
        $this->user = $user;
        $this->messageIds = $messageIds;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->chatGroup->uuid),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'chatGroup' => $this->chatGroup,
            'user' => $this->user,
            'messageIds' => $this->messageIds,
            'readAt' => now()->toISOString()
        ];
    }
}
