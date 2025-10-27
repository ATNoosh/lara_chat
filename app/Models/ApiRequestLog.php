<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiRequestLog extends Model
{
    public $timestamps = false; // Only has created_at

    protected $fillable = [
        'project_id',
        'api_key_id',
        'endpoint',
        'method',
        'response_code',
        'response_time_ms',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'response_code' => 'integer',
        'response_time_ms' => 'integer',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    // Helper method to log API request
    public static function logRequest(
        Project $project,
        ApiKey $apiKey,
        string $endpoint,
        string $method,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        int $responseCode = 200,
        ?int $responseTime = null
    ): self {
        return self::create([
            'project_id' => $project->id,
            'api_key_id' => $apiKey->id,
            'endpoint' => $endpoint,
            'method' => $method,
            'response_code' => $responseCode,
            'response_time_ms' => $responseTime,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }

    // Scope for filtering by date range
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Scope for filtering by response code
    public function scopeSuccessful($query)
    {
        return $query->whereBetween('response_code', [200, 299]);
    }

    public function scopeFailed($query)
    {
        return $query->where('response_code', '>=', 400);
    }
}
