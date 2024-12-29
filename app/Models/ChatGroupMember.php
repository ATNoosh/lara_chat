<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGroupMember extends Model
{
    /** @use HasFactory<\Database\Factories\ChatGroupMemberFactory> */
    use HasFactory;

    protected $fillable = ['chat_group_id', 'user_id'];

}
