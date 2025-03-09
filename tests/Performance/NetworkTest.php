<?php

namespace Tests\Performance;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Models\Asset;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NetworkTest extends TestCase
{
    use RefreshDatabase;

    private $responseMetrics = [];
    private $endpointMetrics = [];
    private $networkIssues = [];
    private $concurrencyMetrics = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and generate token
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        // Seed test data
        $this->seedTestData();
    }

    private function seedTestData()
    {
        Asset::factory()->count(1000)->create();
        WorkOrder::factory()->count(5000)->create();
    }

    /** @test */
    public function analyze_network_performance()
    {
        $this->measureEndpointLatency();
        $this->testConcurrentRequests();
        $this->analyzePayloadSizes();
        $this->testNetworkResilience();
        $this->generateNetworkReport();
    }

    private function measureEndpointLatency()
    {
        $endpoints = [
            'assets.index' => ['GET', '/api/assets'],
            'assets.show' => ['GET', '/api/assets/1'],
            'work_orders.index' => ['GET', '/api/work-orders'],
            'work_orders.show' => ['GET', '/api/work-orders/1'],
            'dashboard.statistics' => ['GET', '/api/dashboard/statistics'],
            'reports.generate' => ['POST', '/api/reports/generate'],
        ];

        foreach ($endpoints as $name => [$method, $url]) {
            $metrics = $this->measureEndpoint($method, $url);
            $this->endpointMetrics[$name] = $metrics;

            // Check for slow responses
            if ($metrics['average_time'] > 1000) { // More than 1 second
                $this->networkIssues[] = [
                    'type' => 'slow_response',
                    'endpoint' => $name,
                    'average_time' => $metrics['average_time'],
                    'suggestion' => 'Consider implementing caching or optimization'
                ];
            }
        }
    }

    private function measureEndpoint($method, $url, $samples = 10)
    {
        $times = collect(range(1, $samples))->map(function () use ($method, $url) {
            $start = microtime(true);
            
            $response = $this->json($method, $url);
            
            $time = (microtime(true) - $start) * 1000; // Convert to milliseconds
            
            return [
                'time' => $time,
                'status' => $response->status(),
                'size' => strlen($response->getContent())
            ];
        });

        return [
            'min_time' => $times->min('time'),
            'max_time' => $times->max('time'),
            'average_time' => $times->average('time'),
            'median_time' => $times->median('time'),
            'p95_time' => $this->calculatePercentile($times->pluck('time'), 95),
            'success_rate' => $times->where('status', 200)->count() / $samples * 100,
            'average_size' => $times->average('size')
        ];
    }

    private function testConcurrentRequests()
    {
        $concurrencyLevels = [5, 10, 20];
        
        foreach ($concurrencyLevels as $concurrency) {
            $start = microtime(true);
            
            // Simulate concurrent requests
            $promises = [];
            for ($i = 0; $i < $concurrency; $i++) {
                $promises[] = $this->async(function () {
                    return $this->json('GET', '/api/assets');
                });
            }

            // Wait for all requests to complete
            $responses = $this->awaitPromises($promises);
            
            $time = (microtime(true) - $start) * 1000;
            
            $this->concurrencyMetrics[$concurrency] = [
                'total_time' => $time,
                'average_time' => $time / $concurrency,
                'success_rate' => collect($responses)
                    ->where('status', 200)
                    ->count() / $concurrency * 100,
                'error_rate' => collect($responses)
                    ->where('status', '!=', 200)
                    ->count() / $concurrency * 100
            ];

            // Check for degradation under load
            if ($this->concurrencyMetrics[$concurrency]['average_time'] > 
                $this->concurrencyMetrics[$concurrency > 5 ? 5 : $concurrency]['average_time'] * 2) {
                $this->networkIssues[] = [
                    'type' => 'concurrency_degradation',
                    'concurrency_level' => $concurrency,
                    'average_time' => $this->concurrencyMetrics[$concurrency]['average_time'],
                    'suggestion' => 'Performance degrades significantly under load. Consider implementing rate limiting or scaling'
                ];
            }
        }
    }

    private function analyzePayloadSizes()
    {
        $endpoints = [
            ['GET', '/api/assets', null],
            ['GET', '/api/work-orders', null],
            ['POST', '/api/work-orders', [
                'title' => 'Test Work Order',
                'description' => 'Test description',
                'priority' => 'high',
                'due_date' => now()->addDays(7)->format('Y-m-d')
            ]],
            ['GET', '/api/reports', null]
        ];

        foreach ($endpoints as [$method, $url, $data]) {
            $response = $this->json($method, $url, $data ?? []);
            $size = strlen($response->getContent());
            
            $this->responseMetrics[$url] = [
                'method' => $method,
                'size' => $size,
                'compressed_size' => $this->measureCompressedSize($response->getContent())
            ];

            // Check for large payloads
            if ($size > 1024 * 1024) { // More than 1MB
                $this->networkIssues[] = [
                    'type' => 'large_payload',
                    'endpoint' => $url,
                    'size' => $size,
                    'suggestion' => 'Consider implementing pagination or reducing payload size'
                ];
            }
        }
    }

    private function testNetworkResilience()
    {
        // Test timeout handling
        $this->testTimeoutHandling();
        
        // Test error handling
        $this->testErrorHandling();
        
        // Test rate limiting
        $this->testRateLimiting();
    }

    private function testTimeoutHandling()
    {
        $response = $this->json('GET', '/api/assets', ['simulate_timeout' => true]);
        
        if ($response->status() !== 504) {
            $this->networkIssues[] = [
                'type' => 'timeout_handling',
                'status' => $response->status(),
                'suggestion' => 'Implement proper timeout handling with appropriate status codes'
            ];
        }
    }

    private function testErrorHandling()
    {
        $errorScenarios = [
            ['GET', '/api/assets/999999', 404],
            ['POST', '/api/work-orders', 422],
            ['GET', '/api/invalid-endpoint', 404]
        ];

        foreach ($errorScenarios as [$method, $url, $expectedStatus]) {
            $response = $this->json($method, $url);
            
            if ($response->status() !== $expectedStatus) {
                $this->networkIssues[] = [
                    'type' => 'error_handling',
                    'endpoint' => $url,
                    'expected_status' => $expectedStatus,
                    'actual_status' => $response->status(),
                    'suggestion' => 'Implement proper error handling with appropriate status codes'
                ];
            }
        }
    }

    private function testRateLimiting()
    {
        $requests = collect(range(1, 60))->map(function () {
            return $this->json('GET', '/api/assets');
        });

        $tooManyRequests = $requests->where('status', 429)->count();
        
        if ($tooManyRequests === 0) {
            $this->networkIssues[] = [
                'type' => 'rate_limiting',
                'suggestion' => 'Implement rate limiting to prevent API abuse'
            ];
        }
    }

    private function generateNetworkReport()
    {
        $report = [
            'endpoint_metrics' => $this->endpointMetrics,
            'concurrency_metrics' => $this->concurrencyMetrics,
            'response_metrics' => $this->responseMetrics,
            'network_issues' => $this->networkIssues,
            'recommendations' => []
        ];

        // Generate recommendations based on collected metrics
        $this->generateRecommendations($report);

        // Save report
        $reportPath = storage_path('logs/network_analysis_report.json');
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));

        // Output summary
        $this->outputSummary($report);
    }

    private function generateRecommendations(&$report)
    {
        // Analyze response times
        foreach ($this->endpointMetrics as $endpoint => $metrics) {
            if ($metrics['p95_time'] > 1000) {
                $report['recommendations'][] = [
                    'type' => 'performance',
                    'endpoint' => $endpoint,
                    'metric' => 'p95_time',
                    'value' => $metrics['p95_time'],
                    'suggestion' => 'Consider implementing caching or optimization'
                ];
            }
        }

        // Analyze concurrency handling
        foreach ($this->concurrencyMetrics as $level => $metrics) {
            if ($metrics['error_rate'] > 10) {
                $report['recommendations'][] = [
                    'type' => 'reliability',
                    'concurrency_level' => $level,
                    'error_rate' => $metrics['error_rate'],
                    'suggestion' => 'Improve error handling and implement circuit breakers'
                ];
            }
        }

        // Analyze payload sizes
        foreach ($this->responseMetrics as $url => $metrics) {
            $compressionRatio = $metrics['size'] / $metrics['compressed_size'];
            if ($compressionRatio < 2 && $metrics['size'] > 50000) {
                $report['recommendations'][] = [
                    'type' => 'optimization',
                    'endpoint' => $url,
                    'size' => $metrics['size'],
                    'suggestion' => 'Enable or improve response compression'
                ];
            }
        }
    }

    private function outputSummary($report)
    {
        echo "\nNetwork Performance Analysis Summary:\n";
        echo "-----------------------------------\n";
        echo "Endpoints analyzed: " . count($this->endpointMetrics) . "\n";
        echo "Concurrency levels tested: " . count($this->concurrencyMetrics) . "\n";
        echo "Network issues found: " . count($this->networkIssues) . "\n";
        echo "Recommendations: " . count($report['recommendations']) . "\n";

        if (!empty($report['recommendations'])) {
            echo "\nKey Recommendations:\n";
            foreach ($report['recommendations'] as $recommendation) {
                echo "- {$recommendation['suggestion']}\n";
            }
        }

        echo "\nFull report saved to: " . storage_path('logs/network_analysis_report.json') . "\n";
    }

    private function calculatePercentile($values, $percentile)
    {
        sort($values);
        $index = ceil(($percentile / 100) * count($values)) - 1;
        return $values[$index];
    }

    private function measureCompressedSize($content)
    {
        return strlen(gzencode($content));
    }

    private function async($callback)
    {
        return new Promise(function ($resolve) use ($callback) {
            $result = $callback();
            $resolve($result);
        });
    }

    private function awaitPromises($promises)
    {
        return array_map(function ($promise) {
            return $promise->wait();
        }, $promises);
    }
}

class Promise
{
    private $callback;
    private $result;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function wait()
    {
        if (!isset($this->result)) {
            $this->result = call_user_func($this->callback, function ($value) {
                return $value;
            });
        }
        return $this->result;
    }
} 