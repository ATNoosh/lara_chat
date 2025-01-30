<?php

namespace App\Repositories;

use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class ChatMessageRepository
{
    public function sendMessage(User $sender, ChatGroup $group, string $messageText): ChatMessage|null
    {
        $chatMessage = ChatMessage::create(
            [
                'chat_group_id' => $group->id,
                'sender_id' => $sender->id,
                'text' => $messageText
            ]
        );

        return $chatMessage;
    }

    public function getMessages(ChatGroup $chatGroup)
    {
        return $chatGroup->chatMessages;
    }
}
