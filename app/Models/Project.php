<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'owner_id',
        'plan',
        'status',
        'settings',
        'max_end_users',
        'max_messages_per_month',
        'max_conversations',
        'allowed_domains',
    ];

    protected $casts = [
        'settings' => 'array',
        'allowed_domains' => 'array',
        'max_end_users' => 'integer',
        'max_messages_per_month' => 'integer',
        'max_conversations' => 'integer',
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
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function endUsers(): HasMany
    {
        return $this->hasMany(EndUser::class);
    }

    public function chatGroups(): HasMany
    {
        return $this->hasMany(ChatGroup::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function usage(): HasMany
    {
        return $this->hasMany(ProjectUsage::class);
    }

    public function apiRequestLogs(): HasMany
    {
        return $this->hasMany(ApiRequestLog::class);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    // Check if domain is allowed for CORS
    public function isDomainAllowed(string $domain): bool
    {
        if (empty($this->allowed_domains)) {
            return true; // Allow all if not set
        }

        foreach ($this->allowed_domains as $allowedDomain) {
            if ($allowedDomain === '*' || $domain === $allowedDomain) {
                return true;
            }
            // Support wildcard subdomains: *.example.com
            if (str_starts_with($allowedDomain, '*.')) {
                $pattern = str_replace('*.', '', $allowedDomain);
                if (str_ends_with($domain, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    // Get current usage for this month
    public function getCurrentUsage()
    {
        return $this->usage()
            ->where('period_date', now()->startOfMonth()->toDateString())
            ->first();
    }

    // Check if project has reached limits
    public function hasReachedEndUserLimit(): bool
    {
        return $this->endUsers()->count() >= $this->max_end_users;
    }

    public function hasReachedMessageLimit(): bool
    {
        $usage = $this->getCurrentUsage();
        return $usage && $usage->total_messages >= $this->max_messages_per_month;
    }

    public function hasReachedConversationLimit(): bool
    {
        return $this->chatGroups()->count() >= $this->max_conversations;
    }
}
