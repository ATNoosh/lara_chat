<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatGroupMember extends Model
{
    /** @use HasFactory<\Database\Factories\ChatGroupMemberFactory> */
    use HasFactory;

    protected $fillable = ['chat_group_id', 'user_id'];

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chatGroup(): BelongsTo
    {
        return $this->belongsTo(ChatGroup::class);
    }
}
