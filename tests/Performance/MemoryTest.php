<?php

namespace Tests\Performance;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\Asset;
use App\Models\WorkOrder;
use App\Models\MaintenanceSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemoryTest extends TestCase
{
    use RefreshDatabase;

    private $memoryMetrics = [];
    private $gcMetrics = [];
    private $leakSuspects = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        // Enable garbage collection
        gc_enable();
        
        // Record initial memory state
        $this->memoryMetrics['initial'] = [
            'usage' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'gc_runs' => gc_collect_cycles()
        ];

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
    public function analyze_memory_usage()
    {
        $this->measureBulkOperations();
        $this->measureChunkedOperations();
        $this->measureMemoryLeaks();
        $this->analyzeGarbageCollection();
        $this->generateMemoryReport();
    }

    private function measureBulkOperations()
    {
        // Measure memory usage for bulk operations
        $start = memory_get_usage(true);
        
        // Bulk load all assets
        $assets = Asset::with(['workOrders', 'maintenanceSchedules'])->get();
        
        $this->memoryMetrics['bulk_load'] = [
            'operation' => 'Load all assets with relations',
            'memory_used' => memory_get_usage(true) - $start,
            'peak' => memory_get_peak_usage(true),
            'records' => $assets->count()
        ];

        // Force cleanup
        unset($assets);
        gc_collect_cycles();
    }

    private function measureChunkedOperations()
    {
        $start = memory_get_usage(true);
        $totalProcessed = 0;
        $maxMemoryUsed = 0;

        // Process in chunks of 100
        Asset::chunk(100, function($assets) use (&$totalProcessed, &$maxMemoryUsed) {
            foreach ($assets as $asset) {
                // Simulate some processing
                $asset->load(['workOrders', 'maintenanceSchedules']);
                $totalProcessed++;
            }
            
            $currentMemory = memory_get_usage(true);
            $maxMemoryUsed = max($maxMemoryUsed, $currentMemory);
            
            // Force garbage collection after each chunk
            gc_collect_cycles();
        });

        $this->memoryMetrics['chunked_load'] = [
            'operation' => 'Load assets in chunks',
            'memory_used' => $maxMemoryUsed - $start,
            'peak' => memory_get_peak_usage(true),
            'records' => $totalProcessed
        ];
    }

    private function measureMemoryLeaks()
    {
        $iterations = 5;
        $baseMemory = memory_get_usage(true);
        $memoryUsage = [];

        for ($i = 0; $i < $iterations; $i++) {
            // Perform memory-intensive operations
            $this->performMemoryIntensiveOperation();
            
            // Record memory usage after garbage collection
            gc_collect_cycles();
            $memoryUsage[] = memory_get_usage(true);
            
            // Sleep briefly to allow for garbage collection
            usleep(100000); // 100ms
        }

        // Analyze for potential memory leaks
        $this->analyzeMemoryPattern($memoryUsage);
    }

    private function performMemoryIntensiveOperation()
    {
        // Simulate complex queries and data processing
        $result = WorkOrder::with(['asset', 'assignedTo'])
            ->select('work_orders.*')
            ->selectRaw('COUNT(maintenance_schedules.id) as maintenance_count')
            ->leftJoin('maintenance_schedules', 'work_orders.asset_id', '=', 'maintenance_schedules.asset_id')
            ->groupBy('work_orders.id')
            ->get()
            ->map(function($workOrder) {
                // Simulate complex processing
                $workOrder->processed_data = [
                    'metrics' => $this->calculateMetrics($workOrder),
                    'history' => $this->getHistory($workOrder)
                ];
                return $workOrder;
            });

        // Force cleanup
        unset($result);
    }

    private function calculateMetrics($workOrder)
    {
        // Simulate complex calculations
        return [
            'completion_rate' => rand(0, 100) / 100,
            'response_time' => rand(1, 1000),
            'cost_efficiency' => rand(0, 100) / 100
        ];
    }

    private function getHistory($workOrder)
    {
        // Simulate fetching historical data
        return array_map(function($i) {
            return [
                'date' => date('Y-m-d', strtotime("-$i days")),
                'status' => ['pending', 'in_progress', 'completed'][rand(0, 2)],
                'metrics' => $this->calculateMetrics(null)
            ];
        }, range(1, 30));
    }

    private function analyzeMemoryPattern($memoryUsage)
    {
        $increases = 0;
        $totalIncrease = 0;

        for ($i = 1; $i < count($memoryUsage); $i++) {
            $diff = $memoryUsage[$i] - $memoryUsage[$i - 1];
            if ($diff > 0) {
                $increases++;
                $totalIncrease += $diff;
            }
        }

        if ($increases >= count($memoryUsage) - 1) {
            $this->leakSuspects[] = [
                'operation' => 'Memory intensive operation',
                'initial_memory' => $memoryUsage[0],
                'final_memory' => end($memoryUsage),
                'total_increase' => $totalIncrease,
                'pattern' => 'Consistent memory growth detected'
            ];
        }
    }

    private function analyzeGarbageCollection()
    {
        $gcStats = [
            'runs' => 0,
            'collected' => 0,
            'time' => 0
        ];

        for ($i = 0; $i < 5; $i++) {
            $start = microtime(true);
            
            // Force garbage collection and record statistics
            $collected = gc_collect_cycles();
            
            $gcStats['runs']++;
            $gcStats['collected'] += $collected;
            $gcStats['time'] += microtime(true) - $start;

            // Perform some memory allocations
            $this->performMemoryIntensiveOperation();
        }

        $this->gcMetrics = [
            'average_collection_time' => $gcStats['time'] / $gcStats['runs'],
            'total_collected' => $gcStats['collected'],
            'runs' => $gcStats['runs']
        ];
    }

    private function generateMemoryReport()
    {
        $report = [
            'memory_metrics' => $this->memoryMetrics,
            'gc_metrics' => $this->gcMetrics,
            'leak_suspects' => $this->leakSuspects,
            'recommendations' => []
        ];

        // Analyze bulk vs chunked operations
        if (isset($this->memoryMetrics['bulk_load']) && isset($this->memoryMetrics['chunked_load'])) {
            $bulkMemoryPerRecord = $this->memoryMetrics['bulk_load']['memory_used'] / $this->memoryMetrics['bulk_load']['records'];
            $chunkedMemoryPerRecord = $this->memoryMetrics['chunked_load']['memory_used'] / $this->memoryMetrics['chunked_load']['records'];

            if ($bulkMemoryPerRecord > $chunkedMemoryPerRecord * 1.5) {
                $report['recommendations'][] = [
                    'type' => 'optimization',
                    'operation' => 'Bulk loading',
                    'suggestion' => 'Consider using chunked operations for better memory efficiency'
                ];
            }
        }

        // Analyze garbage collection
        if ($this->gcMetrics['average_collection_time'] > 0.1) {
            $report['recommendations'][] = [
                'type' => 'performance',
                'operation' => 'Garbage collection',
                'suggestion' => 'High GC time detected. Consider reducing object allocations'
            ];
        }

        // Analyze memory leaks
        foreach ($this->leakSuspects as $suspect) {
            $report['recommendations'][] = [
                'type' => 'memory_leak',
                'operation' => $suspect['operation'],
                'suggestion' => 'Potential memory leak detected. Review object lifecycle and circular references'
            ];
        }

        // Save report
        $reportPath = storage_path('logs/memory_analysis_report.json');
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));

        // Output summary
        $this->outputSummary($report);
    }

    private function outputSummary($report)
    {
        echo "\nMemory Analysis Report Summary:\n";
        echo "------------------------------\n";
        echo "Initial memory usage: " . $this->formatBytes($this->memoryMetrics['initial']['usage']) . "\n";
        echo "Peak memory usage: " . $this->formatBytes(memory_get_peak_usage(true)) . "\n";
        echo "GC runs: " . $this->gcMetrics['runs'] . "\n";
        echo "Potential memory leaks: " . count($this->leakSuspects) . "\n";
        echo "Recommendations: " . count($report['recommendations']) . "\n";
        
        if (!empty($report['recommendations'])) {
            echo "\nKey Recommendations:\n";
            foreach ($report['recommendations'] as $recommendation) {
                echo "- {$recommendation['suggestion']}\n";
            }
        }
        
        echo "\nFull report saved to: " . storage_path('logs/memory_analysis_report.json') . "\n";
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        return round($bytes / (1024 ** $pow), 2) . ' ' . $units[$pow];
    }
} 