<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    /** @use HasFactory<\Database\Factories\ChatMessageFactory> */
    use HasFactory;

    protected $fillable = ['chat_group_id', 'sender_id', 'text', 'status', 'read_at'];
    
    protected $casts = [
        'read_at' => 'datetime',
    ];
    
    // Message status constants
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function chatGroup(): BelongsTo
    {
        return $this->belongsTo(ChatGroup::class, 'chat_group_id');
    }
    
    // Mark message as delivered
    public function markAsDelivered(): void
    {
        $this->update(['status' => self::STATUS_DELIVERED]);
    }
    
    // Mark message as read
    public function markAsRead(): void
    {
        $this->update([
            'status' => self::STATUS_READ,
            'read_at' => now()
        ]);
    }
    
    // Check if message is read
    public function isRead(): bool
    {
        return $this->status === self::STATUS_READ;
    }
    
    // Check if message is delivered
    public function isDelivered(): bool
    {
        return in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_READ]);
    }
}
