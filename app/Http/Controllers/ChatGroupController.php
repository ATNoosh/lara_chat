<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChatGroupRequest;
use App\Http\Requests\UpdateChatGroupRequest;
use App\Models\ChatGroup;

class ChatGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        
    }

    /**
     * Display the specified resource.
     */
    public function show(ChatGroup $chatGroup)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChatGroup $chatGroup)
    {
        //
    }
}
