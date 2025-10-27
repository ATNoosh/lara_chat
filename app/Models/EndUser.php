<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EndUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'project_id',
        'user_identifier',
        'name',
        'email',
        'phone',
        'avatar',
        'is_anonymous',
        'is_verified',
        'metadata',
        'session_token',
        'last_seen_at',
        'last_seen_ip',
        'user_agent',
        'country',
        'city',
        'timezone',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_anonymous' => 'boolean',
        'is_verified' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    protected $hidden = [
        'session_token',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function chatGroups(): BelongsToMany
    {
        return $this->belongsToMany(ChatGroup::class, 'chat_group_members', 'end_user_id', 'chat_group_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'end_user_id');
    }

    public function createdChatGroups(): HasMany
    {
        return $this->hasMany(ChatGroup::class, 'creator_end_user_id');
    }

    // Helper methods
    public function isOnline(): bool
    {
        if (!$this->last_seen_at) {
            return false;
        }

        // Consider online if seen in last 5 minutes
        return $this->last_seen_at->diffInMinutes(now()) < 5;
    }

    public function updateLastSeen(?string $ipAddress = null, ?string $userAgent = null): void
    {
        $data = ['last_seen_at' => now()];
        
        if ($ipAddress) {
            $data['last_seen_ip'] = $ipAddress;
        }
        
        if ($userAgent) {
            $data['user_agent'] = $userAgent;
        }

        $this->update($data);
    }

    // Generate session token for widget
    public function generateSessionToken(): string
    {
        $token = Str::random(60);
        $this->update(['session_token' => hash('sha256', $token)]);
        
        return $token;
    }

    // Verify session token
    public function verifySessionToken(string $token): bool
    {
        return $this->session_token === hash('sha256', $token);
    }

    // Find or create end user
    public static function findOrCreateByIdentifier(Project $project, string $identifier, array $attributes = []): self
    {
        $endUser = self::where('project_id', $project->id)
            ->where('user_identifier', $identifier)
            ->first();

        if ($endUser) {
            // Update attributes if provided
            if (!empty($attributes)) {
                $endUser->update($attributes);
            }
            return $endUser;
        }

        // Create new end user
        return self::create(array_merge($attributes, [
            'project_id' => $project->id,
            'user_identifier' => $identifier,
        ]));
    }

    // Create anonymous user
    public static function createAnonymous(Project $project, array $attributes = []): self
    {
        return self::create(array_merge($attributes, [
            'project_id' => $project->id,
            'is_anonymous' => true,
            'name' => $attributes['name'] ?? 'Guest',
        ]));
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeAnonymous($query)
    {
        return $query->where('is_anonymous', true);
    }

    public function scopeRegistered($query)
    {
        return $query->where('is_anonymous', false);
    }

    public function scopeOnline($query)
    {
        return $query->where('last_seen_at', '>', now()->subMinutes(5));
    }
}
