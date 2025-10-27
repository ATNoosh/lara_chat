<?php

namespace App\Http\Middleware;

use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use App\Models\ChatMessage;
use App\Models\EndUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantScope
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures that all queries are scoped to the current project (tenant).
     * It should be applied to routes that need multi-tenant isolation.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get project from request (set by ApiKeyAuth or other auth middleware)
        $project = $request->get('tenant_project');

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project context is required',
            ], 400);
        }

        // Check if project is active
        if (!$project->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Project is not active',
            ], 403);
        }

        // Store project in app container for easy access
        app()->instance('current_project', $project);

        // Apply global scopes to tenant-aware models
        $this->applyTenantScopes($project);

        return $next($request);
    }

    /**
     * Apply global scopes to all tenant-aware models
     */
    protected function applyTenantScopes($project): void
    {
        // Apply to ChatGroup
        ChatGroup::addGlobalScope('tenant', function ($query) use ($project) {
            $query->where('chat_groups.project_id', $project->id);
        });

        // Apply to ChatMessage
        ChatMessage::addGlobalScope('tenant', function ($query) use ($project) {
            $query->where('chat_messages.project_id', $project->id);
        });

        // Apply to EndUser
        EndUser::addGlobalScope('tenant', function ($query) use ($project) {
            $query->where('end_users.project_id', $project->id);
        });

        // Apply to ChatGroupMember
        ChatGroupMember::addGlobalScope('tenant', function ($query) use ($project) {
            $query->where('chat_group_members.project_id', $project->id);
        });
    }
}
