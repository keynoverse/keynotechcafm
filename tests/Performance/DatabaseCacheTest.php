<?php

namespace Tests\Performance;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Models\Asset;
use App\Models\WorkOrder;
use App\Models\MaintenanceSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseCacheTest extends TestCase
{
    use RefreshDatabase;

    private $cacheMetrics = [];
    private $queryTimes = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear all caches before testing
        Cache::flush();
        if (config('cache.default') === 'redis') {
            Redis::flushall();
        }

        // Seed test data
        $this->seedTestData();
    }

    private function seedTestData()
    {
        Asset::factory()->count(1000)->create();
        WorkOrder::factory()->count(5000)->create();
        MaintenanceSchedule::factory()->count(2000)->create();
    }

    /** @test */
    public function analyze_query_cache_performance()
    {
        $this->measureUncachedQueries();
        $this->measureCachedQueries();
        $this->analyzeComplexQueries();
        $this->testCacheInvalidation();
        $this->generateCacheReport();
    }

    private function measureUncachedQueries()
    {
        // Test common queries without caching
        $queries = [
            'assets_by_status' => fn() => Asset::where('status', 'active')->get(),
            'work_orders_by_priority' => fn() => WorkOrder::where('priority', 'high')->get(),
            'maintenance_schedules_due' => fn() => MaintenanceSchedule::where('next_due_date', '<=', now())->get()
        ];

        foreach ($queries as $name => $query) {
            $start = microtime(true);
            $query();
            $this->queryTimes[$name]['uncached'] = microtime(true) - $start;
        }
    }

    private function measureCachedQueries()
    {
        $queries = [
            'assets_by_status' => [
                'query' => fn() => Asset::where('status', 'active')->get(),
                'key' => 'assets.active',
                'ttl' => 3600
            ],
            'work_orders_by_priority' => [
                'query' => fn() => WorkOrder::where('priority', 'high')->get(),
                'key' => 'work_orders.high_priority',
                'ttl' => 1800
            ],
            'maintenance_schedules_due' => [
                'query' => fn() => MaintenanceSchedule::where('next_due_date', '<=', now())->get(),
                'key' => 'maintenance_schedules.due',
                'ttl' => 900
            ]
        ];

        foreach ($queries as $name => $config) {
            // First access (cache miss)
            $start = microtime(true);
            $result = Cache::remember($config['key'], $config['ttl'], $config['query']);
            $this->queryTimes[$name]['cache_miss'] = microtime(true) - $start;

            // Second access (cache hit)
            $start = microtime(true);
            $result = Cache::get($config['key']);
            $this->queryTimes[$name]['cache_hit'] = microtime(true) - $start;

            $this->cacheMetrics[$name] = [
                'size' => strlen(serialize($result)),
                'ttl' => $config['ttl']
            ];
        }
    }

    private function analyzeComplexQueries()
    {
        // Test complex query with joins and aggregations
        $complexQuery = fn() => WorkOrder::with(['asset', 'assignedTo'])
            ->select('work_orders.*')
            ->selectRaw('COUNT(maintenance_schedules.id) as maintenance_count')
            ->leftJoin('maintenance_schedules', 'work_orders.asset_id', '=', 'maintenance_schedules.asset_id')
            ->where('work_orders.status', 'open')
            ->groupBy('work_orders.id')
            ->having('maintenance_count', '>', 0)
            ->get();

        // Measure uncached performance
        $start = microtime(true);
        $complexQuery();
        $this->queryTimes['complex_query']['uncached'] = microtime(true) - $start;

        // Measure cached performance
        $start = microtime(true);
        $result = Cache::remember('complex.work_orders', 3600, $complexQuery);
        $this->queryTimes['complex_query']['cache_miss'] = microtime(true) - $start;

        $start = microtime(true);
        Cache::get('complex.work_orders');
        $this->queryTimes['complex_query']['cache_hit'] = microtime(true) - $start;

        $this->cacheMetrics['complex_query'] = [
            'size' => strlen(serialize($result)),
            'ttl' => 3600
        ];
    }

    private function testCacheInvalidation()
    {
        // Test cache invalidation scenarios
        $scenarios = [
            'single_record' => [
                'setup' => fn() => Cache::remember('asset.1', 3600, fn() => Asset::find(1)),
                'invalidate' => fn() => Asset::find(1)->update(['status' => 'inactive']),
                'key' => 'asset.1'
            ],
            'collection' => [
                'setup' => fn() => Cache::remember('assets.active', 3600, fn() => Asset::where('status', 'active')->get()),
                'invalidate' => fn() => Asset::where('status', 'active')->update(['status' => 'inactive']),
                'key' => 'assets.active'
            ]
        ];

        foreach ($scenarios as $name => $scenario) {
            // Setup cache
            $scenario['setup']();

            // Measure invalidation time
            $start = microtime(true);
            $scenario['invalidate']();
            Cache::forget($scenario['key']);
            $this->queryTimes[$name]['invalidation'] = microtime(true) - $start;
        }
    }

    private function generateCacheReport()
    {
        $report = [
            'query_times' => $this->queryTimes,
            'cache_metrics' => $this->cacheMetrics,
            'recommendations' => []
        ];

        // Analyze and generate recommendations
        foreach ($this->queryTimes as $query => $times) {
            // Calculate performance improvements
            $uncachedTime = $times['uncached'] ?? 0;
            $cacheHitTime = $times['cache_hit'] ?? 0;
            $improvement = $uncachedTime > 0 ? (($uncachedTime - $cacheHitTime) / $uncachedTime) * 100 : 0;

            if ($improvement > 50) {
                $report['recommendations'][] = [
                    'query' => $query,
                    'improvement' => round($improvement, 2) . '%',
                    'suggestion' => 'Consider implementing caching for this query'
                ];
            }

            // Analyze cache size
            if (isset($this->cacheMetrics[$query]['size']) && $this->cacheMetrics[$query]['size'] > 1024 * 1024) {
                $report['recommendations'][] = [
                    'query' => $query,
                    'size' => round($this->cacheMetrics[$query]['size'] / (1024 * 1024), 2) . 'MB',
                    'suggestion' => 'Consider implementing pagination or reducing cached data size'
                ];
            }
        }

        // Save report
        $reportPath = storage_path('logs/cache_performance_report.json');
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));

        // Output summary
        $this->outputSummary($report);
    }

    private function outputSummary($report)
    {
        echo "\nCache Performance Report Summary:\n";
        echo "--------------------------------\n";
        echo "Queries analyzed: " . count($this->queryTimes) . "\n";
        echo "Cache entries analyzed: " . count($this->cacheMetrics) . "\n";
        echo "Recommendations: " . count($report['recommendations']) . "\n";
        echo "\nHighlights:\n";
        
        foreach ($report['recommendations'] as $recommendation) {
            echo "- {$recommendation['query']}: {$recommendation['suggestion']}\n";
        }
        
        echo "\nFull report saved to: " . storage_path('logs/cache_performance_report.json') . "\n";
    }
} 