<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatGroup extends Model
{
    /** @use HasFactory<\Database\Factories\ChatGroupFactory> */
    use HasFactory;

    protected $fillable = ['name', 'creator_id'];

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'chat_group_id');
    }
}
