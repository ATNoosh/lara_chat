<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChatMessageRequest;
use App\Http\Requests\UpdateChatMessageRequest;
use App\Models\ChatMessage;
use App\Repositories\ChatMessageRepository;

class ChatMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($groupId)
    {
        try {
            $chatGroup = \App\Models\ChatGroup::findOrFail($groupId);
            $user = auth()->user();
            
            // Check if user is member of this group
            if (!$user->chatGroups()->where('chat_groups.id', $chatGroup->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a member of this chat group'
                ], 403);
            }

            $messages = app(ChatMessageRepository::class)->getMessages($chatGroup);
            
            return response()->json([
                'success' => true,
                'data' => $messages->load('sender')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
    public function store(StoreChatMessageRequest $request)
    {
        try {
            $chatGroup = \App\Models\ChatGroup::findOrFail($request->route('group_id'));
            $user = auth()->user();
            
            // Check if user is member of this group
            if (!$user->chatGroups()->where('chat_groups.id', $chatGroup->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a member of this chat group'
                ], 403);
            }

            $message = app(ChatMessageRepository::class)->sendMessage($user, $chatGroup, $request->message);
            
            // Broadcast the message
            broadcast(new \App\Events\MessageSent($message))->toOthers();
            
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message->load('sender')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
     * Remove the specified resource from storage.
     */
    public function destroy(ChatMessage $chatMessage)
    {
        //
    }
}
