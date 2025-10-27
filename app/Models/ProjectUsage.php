<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectUsage extends Model
{
    use HasFactory;

    protected $table = 'project_usage';

    protected $fillable = [
        'project_id',
        'period_date',
        'total_end_users',
        'active_end_users',
        'total_conversations',
        'active_conversations',
        'total_messages',
        'api_requests',
        'websocket_connections',
        'storage_used',
    ];

    protected $casts = [
        'period_date' => 'date',
        'total_end_users' => 'integer',
        'active_end_users' => 'integer',
        'total_conversations' => 'integer',
        'active_conversations' => 'integer',
        'total_messages' => 'integer',
        'api_requests' => 'integer',
        'websocket_connections' => 'integer',
        'storage_used' => 'integer',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Get or create usage record for current month
    public static function getOrCreateForMonth(Project $project, ?string $date = null): self
    {
        $periodDate = $date ? now()->parse($date)->startOfMonth() : now()->startOfMonth();

        return self::firstOrCreate(
            [
                'project_id' => $project->id,
                'period_date' => $periodDate->toDateString(),
            ],
            [
                'total_end_users' => 0,
                'active_end_users' => 0,
                'total_conversations' => 0,
                'active_conversations' => 0,
                'total_messages' => 0,
                'api_requests' => 0,
                'websocket_connections' => 0,
                'storage_used' => 0,
            ]
        );
    }

    // Increment counters
    public function incrementMessages(int $count = 1): void
    {
        $this->increment('total_messages', $count);
    }

    public function incrementApiRequests(int $count = 1): void
    {
        $this->increment('api_requests', $count);
    }

    public function incrementWebSocketConnections(int $count = 1): void
    {
        $this->increment('websocket_connections', $count);
    }

    // Update snapshots (run daily via scheduled task)
    public function updateSnapshots(): void
    {
        $this->update([
            'total_end_users' => $this->project->endUsers()->count(),
            'active_end_users' => $this->project->endUsers()
                ->whereHas('sentMessages', function ($query) {
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                })
                ->count(),
            'total_conversations' => $this->project->chatGroups()->count(),
            'active_conversations' => $this->project->chatGroups()
                ->whereHas('chatMessages', function ($query) {
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                })
                ->count(),
        ]);
    }
}
