<?php

use Illuminate\Support\Facades\Broadcast;

// Register broadcasting auth route; keep 'web' and add Sanctum token guard
Broadcast::routes(['middleware' => ['web', 'auth:sanctum']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatGroupId}', function ($user, $chatGroupId) {
    return $user->chatGroups()->where('chat_groups.id', $chatGroupId)->exists();
});