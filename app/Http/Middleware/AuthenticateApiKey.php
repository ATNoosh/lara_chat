<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\ApiRequestLog;
use App\Models\Project;
use App\Models\ProjectUsage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     *
     * This middleware authenticates requests using API keys.
     * The API key should be provided in the Authorization header as: Bearer {api_key}
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $requiredScope = null): Response
    {
        $apiKey = $this->extractApiKey($request);

        if (!$apiKey) {
            return $this->unauthorizedResponse('API key is required. Provide it in Authorization header as: Bearer {api_key}');
        }

        $apiKeyModel = $this->findApiKey($apiKey);

        if (!$apiKeyModel || !$apiKeyModel->verify($apiKey)) {
            return $this->unauthorizedResponse('Invalid API key');
        }

        if (!$apiKeyModel->isValid()) {
            return $this->unauthorizedResponse('API key is expired or inactive');
        }

        $project = $apiKeyModel->project;

        $validationError = $this->validateProject($project, $apiKeyModel, $requiredScope);
        if ($validationError) {
            return $validationError;
        }

        $this->trackUsage($project, $apiKeyModel, $request);
        $this->attachToRequest($request, $project, $apiKeyModel);

        return $next($request);
    }

    /**
     * Find API key by prefix
     */
    protected function findApiKey(string $apiKey): ?ApiKey
    {
        $keyPrefix = $this->extractPrefix($apiKey);
        
        return ApiKey::where('key_prefix', $keyPrefix)
            ->active()
            ->first();
    }

    /**
     * Validate project and permissions
     */
    protected function validateProject(?Project $project, ApiKey $apiKeyModel, ?string $requiredScope): ?Response
    {
        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }

        if (!$project->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Project is suspended or cancelled',
            ], 403);
        }

        if ($requiredScope && !$apiKeyModel->hasScope($requiredScope)) {
            return response()->json([
                'success' => false,
                'message' => 'API key does not have required scope: ' . $requiredScope,
            ], 403);
        }

        return null;
    }

    /**
     * Track API key usage and analytics
     */
    protected function trackUsage(Project $project, ApiKey $apiKeyModel, Request $request): void
    {
        // Update last used timestamp (async to avoid blocking)
        dispatch(function () use ($apiKeyModel, $request) {
            $apiKeyModel->markAsUsed($request->ip());
        })->afterResponse();

        // Track API request for analytics
        $this->trackApiRequest($project, $apiKeyModel, $request);
    }

    /**
     * Attach project and API key to request
     */
    protected function attachToRequest(Request $request, Project $project, ApiKey $apiKeyModel): void
    {
        $request->merge([
            'tenant_project' => $project,
            'api_key' => $apiKeyModel,
        ]);
    }

    /**
     * Return unauthorized response
     */
    protected function unauthorizedResponse(string $message): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 401);
    }

    /**
     * Extract API key from request
     */
    protected function extractApiKey(Request $request): ?string
    {
        // Try Authorization header first
        $header = $request->header('Authorization');
        
        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        // Fallback to query parameter (not recommended for production)
        return $request->query('api_key');
    }

    /**
     * Extract prefix from API key
     */
    protected function extractPrefix(string $apiKey): string
    {
        $parts = explode('_', $apiKey);
        
        if (count($parts) >= 3) {
            return $parts[0] . '_' . $parts[1] . '_';
        }

        return '';
    }

    /**
     * Track API request for analytics
     */
    protected function trackApiRequest(Project $project, ApiKey $apiKey, Request $request): void
    {
        // Queue this to avoid blocking the request
        dispatch(function () use ($project, $apiKey, $request) {
            ApiRequestLog::logRequest(
                project: $project,
                apiKey: $apiKey,
                endpoint: $request->path(),
                method: $request->method(),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            // Increment usage counter
            $usage = ProjectUsage::getOrCreateForMonth($project);
            $usage->incrementApiRequests();
        })->afterResponse();
    }
}
