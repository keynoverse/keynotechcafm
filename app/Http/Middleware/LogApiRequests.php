<?php

namespace App\Http\Middleware;

use App\Services\LoggingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate a unique request ID
        $requestId = uniqid('req_');
        $request->headers->set('X-Request-ID', $requestId);

        // Start timing the request
        $startTime = microtime(true);

        // Log the incoming request
        LoggingService::logApiRequest(
            $request->method(),
            $request->path(),
            $request->except(['password', 'password_confirmation']),
            $request->user()?->id
        );

        // Process the request
        $response = $next($request);

        // Calculate request duration
        $duration = microtime(true) - $startTime;

        // Log performance metric
        LoggingService::logPerformance('request_duration', $duration, [
            'path' => $request->path(),
            'method' => $request->method(),
            'status' => $response->getStatusCode(),
            'request_id' => $requestId
        ]);

        // Add request ID to response headers
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
} 