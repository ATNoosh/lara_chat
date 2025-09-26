<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChatGroupRequest;
use App\Http\Requests\UpdateChatGroupRequest;
use App\Models\ChatGroup;
use App\Repositories\ChatGroupRepository;

class ChatGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $chatGroups = $user->chatGroups()->with(['members', 'creator', 'chatMessages' => function ($query) {
            $query->latest()->limit(1);
        }])->get();

        return response()->json([
            'success' => true,
            'data' => $chatGroups,
        ]);
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
    public function store(StoreChatGroupRequest $request)
    {
        try {
            if ($request->filled('memberIds')) {
                $chatGroup = app(ChatGroupRepository::class)->createGroupWithMembers(
                    auth()->id(),
                    $request->memberIds,
                    ['name' => $request->input('name'), 'type' => ChatGroup::TYPE_SIMPLE]
                );
                // Notify all members about the new group
                foreach ($chatGroup->members as $member) {
                    event(new \App\Events\GroupAdded($chatGroup, $member->id));
                }
            } else {
                $chatGroup = app(ChatGroupRepository::class)->createFaceToFaceGroup(auth()->id(), $request->secondUserId);
                // Notify both participants
                foreach ($chatGroup->members as $member) {
                    event(new \App\Events\GroupAdded($chatGroup, $member->id));
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Chat group created successfully',
                'data' => $chatGroup->load(['members', 'creator']),
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
    public function show(ChatGroup $chatGroup)
    {
        $user = auth()->user();

        // Check if user is member of this group
        if (! $user->chatGroups()->where('chat_groups.id', $chatGroup->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this chat group',
            ], 403);
        }

        $chatGroup->load(['members', 'creator', 'chatMessages.sender']);

        return response()->json([
            'success' => true,
            'data' => $chatGroup,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChatGroup $chatGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatGroupRequest $request, ChatGroup $chatGroup)
    {
        try {
            if ($request->filled('name')) {
                $chatGroup->name = $request->input('name');
                $chatGroup->save();
            }

            if ($request->filled('memberIds')) {
                // Ensure creator is always included
                $memberIds = array_unique(array_merge([$chatGroup->creator_id], $request->memberIds));
                app(ChatGroupRepository::class)->setGroupMembers($chatGroup->id, $memberIds);
                $chatGroup->load('members');
            }

            return response()->json([
                'success' => true,
                'message' => 'Chat group updated successfully',
                'data' => $chatGroup->load(['members', 'creator']),
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
    public function destroy(ChatGroup $chatGroup)
    {
        //
    }
}
