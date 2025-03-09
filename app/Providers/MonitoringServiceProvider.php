<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider;
use Barryvdh\Debugbar\ServiceProvider as DebugbarServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Events\QueryExecuted;

class MonitoringServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            // Register Telescope
            if (config('monitoring.telescope.enabled')) {
                $this->app->register(TelescopeServiceProvider::class);
            }

            // Register Debugbar
            if (config('monitoring.debugbar.enabled')) {
                $this->app->register(DebugbarServiceProvider::class);
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->environment('local')) {
            $this->setupQueryLogging();
            $this->setupPerformanceMonitoring();
        }
    }

    /**
     * Set up query logging for development.
     */
    protected function setupQueryLogging(): void
    {
        if (config('monitoring.logging.slow_queries.enabled')) {
            DB::listen(function (QueryExecuted $query) {
                $threshold = config('monitoring.logging.slow_queries.threshold', 1000);
                
                if ($query->time > $threshold) {
                    Log::warning('Slow query detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time,
                    ]);
                }
            });
        }
    }

    /**
     * Set up performance monitoring for development.
     */
    protected function setupPerformanceMonitoring(): void
    {
        if (config('monitoring.logging.performance.enabled')) {
            $startTime = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
            
            register_shutdown_function(function () use ($startTime) {
                $endTime = microtime(true);
                $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
                $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024; // Convert to MB
                
                $threshold = config('monitoring.logging.performance.threshold', 1000);
                $memoryLimit = config('monitoring.logging.performance.memory_limit', 128);
                
                if ($executionTime > $threshold || $memoryUsage > $memoryLimit) {
                    Log::warning('Performance threshold exceeded', [
                        'execution_time' => $executionTime,
                        'memory_usage' => $memoryUsage,
                        'url' => request()->fullUrl(),
                        'method' => request()->method(),
                    ]);
                }
            });
        }
    }
} 