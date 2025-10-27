<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'key_prefix',
        'key_hash',
        'key_hint',
        'scopes',
        'environment',
        'last_used_at',
        'last_used_ip',
        'expires_at',
        'is_active',
        'rate_limit_per_minute',
    ];

    protected $casts = [
        'scopes' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'rate_limit_per_minute' => 'integer',
    ];

    protected $hidden = [
        'key_hash',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Generate a new API key
    public static function generate(Project $project, string $name, array $scopes = [], string $environment = 'live'): array
    {
        // Generate random key
        $key = self::generateRandomKey($environment);
        
        // Extract prefix and hint
        $parts = explode('_', $key);
        $prefix = $parts[0] . '_' . $parts[1] . '_';
        $secret = $parts[2];
        $hint = substr($secret, -4);

        // Create API key record
        $apiKey = self::create([
            'project_id' => $project->id,
            'name' => $name,
            'key_prefix' => $prefix,
            'key_hash' => Hash::make($key),
            'key_hint' => $hint,
            'scopes' => $scopes,
            'environment' => $environment,
            'is_active' => true,
            'rate_limit_per_minute' => 60,
        ]);

        return [
            'model' => $apiKey,
            'plain_text_key' => $key,
        ];
    }

    // Generate random key string
    private static function generateRandomKey(string $environment): string
    {
        $prefix = $environment === 'live' ? 'pk_live' : 'pk_test';
        $random = Str::random(40);
        
        return "{$prefix}_{$random}";
    }

    // Verify if provided key matches this API key
    public function verify(string $key): bool
    {
        return Hash::check($key, $this->key_hash);
    }

    // Check if key is valid (active and not expired)
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    // Check if key has specific scope
    public function hasScope(string $scope): bool
    {
        if (empty($this->scopes)) {
            return true; // Full access if no scopes defined
        }

        // Check for wildcard
        if (in_array('*', $this->scopes)) {
            return true;
        }

        // Check for exact match
        if (in_array($scope, $this->scopes)) {
            return true;
        }

        // Check for pattern match (e.g., 'conversations:*')
        foreach ($this->scopes as $allowedScope) {
            if (str_ends_with($allowedScope, ':*')) {
                $pattern = str_replace(':*', '', $allowedScope);
                if (str_starts_with($scope, $pattern . ':')) {
                    return true;
                }
            }
        }

        return false;
    }

    // Update last used timestamp
    public function markAsUsed(?string $ipAddress = null): void
    {
        $this->update([
            'last_used_at' => now(),
            'last_used_ip' => $ipAddress,
        ]);
    }

    // Revoke (deactivate) the key
    public function revoke(): void
    {
        $this->update(['is_active' => false]);
    }

    // Scope queries
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeLive($query)
    {
        return $query->where('environment', 'live');
    }

    public function scopeTest($query)
    {
        return $query->where('environment', 'test');
    }
}
