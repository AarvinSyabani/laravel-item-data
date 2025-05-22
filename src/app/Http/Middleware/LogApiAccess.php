<?php

namespace App\Http\Middleware;

use App\Models\AccessLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogApiAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Process the request
        $response = $next($request);
        
        // Log the API access
        $this->logAccess($request, $response);
        
        return $response;
    }
    
    /**
     * Log the API access details
     */
    private function logAccess(Request $request, Response $response): void
    {
        // Don't log OPTIONS requests (CORS pre-flight)
        if ($request->method() === 'OPTIONS') {
            return;
        }
        
        // Determine action based on request method and path
        $action = $this->determineAction($request);
        
        // Determine resource type and ID if applicable
        list($resourceType, $resourceId) = $this->determineResource($request);
        
        // Create the access log entry
        AccessLog::create([
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'action' => $action,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
        ]);
    }
    
    /**
     * Determine the action based on the request
     */
    private function determineAction(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();
        
        if (str_contains($path, 'login')) {
            return 'LOGIN';
        }
        
        if (str_contains($path, 'logout')) {
            return 'LOGOUT';
        }
        
        // Map HTTP methods to actions
        $actionMap = [
            'GET' => 'VIEW',
            'POST' => 'CREATE',
            'PUT' => 'UPDATE',
            'PATCH' => 'UPDATE',
            'DELETE' => 'DELETE',
        ];
        
        return $actionMap[$method] ?? 'OTHER';
    }
    
    /**
     * Determine the resource type and ID from the request
     * 
     * @return array [resource_type, resource_id]
     */
    private function determineResource(Request $request): array
    {
        $path = $request->path();
        $segments = explode('/', $path);
        
        // Skip the 'api' prefix if present
        if ($segments[0] === 'api') {
            array_shift($segments);
        }
        
        // If we have at least 2 segments, the first is likely the resource type
        // and the second could be the ID (if numeric)
        if (count($segments) >= 2) {
            $resourceType = $segments[0];
            $potentialId = $segments[1];
            
            // Check if the second segment is numeric (likely an ID)
            if (is_numeric($potentialId)) {
                return [$resourceType, $potentialId];
            }
            
            // Otherwise just return the resource type
            return [$resourceType, null];
        }
        
        // If we just have one segment, it's likely just the resource type
        if (count($segments) === 1) {
            return [$segments[0], null];
        }
        
        // Default
        return [null, null];
    }
}
