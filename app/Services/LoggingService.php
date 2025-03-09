<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LoggingService
{
    /**
     * Log API request information
     */
    public static function logApiRequest(string $method, string $endpoint, array $params = [], ?string $userId = null): void
    {
        Log::channel('api')->info('API Request', [
            'method' => $method,
            'endpoint' => $endpoint,
            'params' => $params,
            'user_id' => $userId,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Log authentication events
     */
    public static function logAuth(string $event, array $data = []): void
    {
        Log::channel('auth')->info($event, array_merge($data, [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]));
    }

    /**
     * Log maintenance activities
     */
    public static function logMaintenance(string $action, array $data = [], ?string $userId = null): void
    {
        Log::channel('maintenance')->info($action, array_merge($data, [
            'user_id' => $userId,
            'timestamp' => now()->toDateTimeString()
        ]));
    }

    /**
     * Log work order activities
     */
    public static function logWorkOrder(string $action, array $data = [], ?string $userId = null): void
    {
        Log::channel('workorders')->info($action, array_merge($data, [
            'user_id' => $userId,
            'timestamp' => now()->toDateTimeString()
        ]));
    }

    /**
     * Log critical errors
     */
    public static function logError(string $message, array $context = [], string $channel = 'stack'): void
    {
        Log::channel($channel)->error($message, array_merge($context, [
            'timestamp' => now()->toDateTimeString(),
            'request_id' => request()->id() ?? uniqid(),
            'url' => request()->fullUrl(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]));
    }

    /**
     * Log database operations
     */
    public static function logDatabaseOperation(string $operation, string $model, array $data = [], ?string $userId = null): void
    {
        Log::channel('daily')->info('Database Operation', [
            'operation' => $operation,
            'model' => $model,
            'data' => $data,
            'user_id' => $userId,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Log system events
     */
    public static function logSystemEvent(string $event, array $data = []): void
    {
        Log::channel('daily')->info('System Event', array_merge($data, [
            'event' => $event,
            'environment' => app()->environment(),
            'timestamp' => now()->toDateTimeString()
        ]));
    }

    /**
     * Log performance metrics
     */
    public static function logPerformance(string $metric, float $value, array $context = []): void
    {
        Log::channel('daily')->info('Performance Metric', array_merge($context, [
            'metric' => $metric,
            'value' => $value,
            'timestamp' => now()->toDateTimeString()
        ]));
    }

    /**
     * Log security events
     */
    public static function logSecurityEvent(string $event, array $data = []): void
    {
        Log::channel('daily')->warning('Security Event', array_merge($data, [
            'event' => $event,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString()
        ]));
    }

    /**
     * Log file operations
     */
    public static function logFileOperation(string $operation, string $path, array $context = []): void
    {
        Log::channel('daily')->info('File Operation', array_merge($context, [
            'operation' => $operation,
            'path' => $path,
            'timestamp' => now()->toDateTimeString()
        ]));
    }
} 