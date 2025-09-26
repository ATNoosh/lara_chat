<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChatMessageRequest;
use App\Http\Requests\UpdateChatMessageRequest;
use App\Models\ChatMessage;
use App\Models\ChatGroup;
use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Events\UserTyping;
use App\Repositories\ChatMessageRepository;

class ChatMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ChatGroup $chatGroup)
    {
        try {
            $user = auth()->user();

            // Check if user is member of this group
            if (! $user->chatGroups()->where('chat_groups.id', $chatGroup->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a member of this chat group',
                ], 403);
            }

            $messages = app(ChatMessageRepository::class)->getMessages($chatGroup);

            return response()->json([
                'success' => true,
                'data' => $messages->load('sender'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatMessageRequest $request, ChatGroup $chatGroup)
    {
        try {
            $user = auth()->user();

            // Check if user is member of this group
            if (! $user->chatGroups()->where('chat_groups.id', $chatGroup->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a member of this chat group',
                ], 403);
            }

            $message = app(ChatMessageRepository::class)->sendMessage($user, $chatGroup, $request->message);

            // Broadcast the message
            broadcast(new MessageSent($message))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message->load('sender'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ChatMessage $chatMessage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChatMessage $chatMessage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatMessageRequest $request, ChatMessage $chatMessage)
    {
        //
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(ChatGroup $chatGroup)
    {
        try {
            $user = auth()->user();

            // Check if user is member of this group
            if (! $user->chatGroups()->where('chat_groups.id', $chatGroup->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a member of this chat group',
                ], 403);
            }

            // Mark all unread messages in this group as read (except user's own messages)
            $unreadMessages = $chatGroup->chatMessages()
                ->where('sender_id', '!=', $user->id)
                ->where('status', '!=', 'read')
                ->get();

            foreach ($unreadMessages as $message) {
                $message->markAsRead();
            }

            // Broadcast read receipt event
            broadcast(new MessagesRead($chatGroup, $user, $unreadMessages->pluck('id')->toArray()))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Messages marked as read',
                'data' => [
                    'read_count' => $unreadMessages->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mark a specific message as read
     */
    public function markMessageAsRead(ChatGroup $chatGroup, ChatMessage $chatMessage)
    {
        try {
            $user = auth()->user();

            // Check if user is member of this group
            if (! $user->chatGroups()->where('chat_groups.id', $chatGroup->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a member of this chat group',
                ], 403);
            }

            // Check if message belongs to this group
            if ($chatMessage->chat_group_id !== $chatGroup->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message does not belong to this chat group',
                ], 400);
            }

            // Only mark as read if message is not from current user and not already read
            if ($chatMessage->sender_id !== $user->id && $chatMessage->status !== 'read') {
                $chatMessage->markAsRead();

                // Broadcast read receipt event
                broadcast(new MessagesRead($chatGroup, $user, [$chatMessage->id]))->toOthers();

                return response()->json([
                    'success' => true,
                    'message' => 'Message marked as read',
                    'data' => [
                        'message_id' => $chatMessage->id,
                        'status' => $chatMessage->status,
                        'read_at' => $chatMessage->read_at,
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Message already read or is your own message',
                'data' => [
                    'message_id' => $chatMessage->id,
                    'status' => $chatMessage->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update user typing status
     */
    public function updateTypingStatus(ChatGroup $chatGroup, Request $request)
    {
        try {
            $user = auth()->user();

            // Check if user is member of this group
            if (! $user->chatGroups()->where('chat_groups.id', $chatGroup->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a member of this chat group',
                ], 403);
            }

            $isTyping = $request->boolean('is_typing', false);

            // Broadcast typing status
            broadcast(new UserTyping($chatGroup, $user, $isTyping))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Typing status updated',
                'data' => [
                    'is_typing' => $isTyping,
                    'user_id' => $user->id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChatMessage $chatMessage)
    {
        //
    }
}
