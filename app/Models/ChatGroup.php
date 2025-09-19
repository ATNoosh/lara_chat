<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChatGroup extends Model
{
    public const TYPE_SIMPLE = 'SIMPLE';
    public const TYPE_FACE_TO_FACE = 'FACE_TO_FACE';
    public const TYPES = [self::TYPE_SIMPLE, self::TYPE_FACE_TO_FACE];
    /** @use HasFactory<\Database\Factories\ChatGroupFactory> */
    use HasFactory;

    protected $fillable = ['name', 'creator_id', 'is_private', 'type', 'uuid'];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }

    public function scopeSimple(Builder $query): void
    {
        $query->where('type', self::TYPE_SIMPLE);
    }

    public function scopeFaceToFace(Builder $query): void
    {
        $query->where('type', self::TYPE_FACE_TO_FACE);
    }

    public function scopeType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'chat_group_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, ChatGroupMember::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
