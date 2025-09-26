<?php

use Illuminate\Support\Facades\Broadcast;

// Register broadcasting auth route; keep 'web' and add Sanctum token guard
Broadcast::routes(['middleware' => ['web', 'auth:sanctum']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatGroupUuid}', function ($user, $chatGroupUuid) {
    return $user->chatGroups()->where('chat_groups.uuid', $chatGroupUuid)->exists();
});
