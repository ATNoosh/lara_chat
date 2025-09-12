<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatGroupId}', function ($user, $chatGroupId) {
    return $user->chatGroups()->where('chat_groups.id', $chatGroupId)->exists();
});