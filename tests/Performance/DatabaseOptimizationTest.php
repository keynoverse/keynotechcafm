<?php

namespace Tests\Performance;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;
use App\Models\Asset;
use App\Models\WorkOrder;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseOptimizationTest extends TestCase
{
    use RefreshDatabase;

    private $queryLog = [];
    private $slowQueries = [];
    private $missingIndexes = [];
    private $tableStats = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Enable query logging
        DB::enableQueryLog();

        // Create test data
        $this->seedTestData();
    }

    private function seedTestData()
    {
        // Create test users
        User::factory()->count(100)->create();

        // Create test assets
        Asset::factory()->count(1000)->create();

        // Create test work orders
        WorkOrder::factory()->count(5000)->create();

        // Create test maintenance schedules
        MaintenanceSchedule::factory()->count(2000)->create();
    }

    /** @test */
    public function analyze_database_performance()
    {
        $this->analyzeTableSizes();
        $this->analyzeIndexUsage();
        $this->analyzeSlowQueries();
        $this->analyzeQueryPatterns();
        $this->checkForeignKeys();
        $this->checkDataTypes();

        // Generate optimization report
        $this->generateOptimizationReport();
    }

    private function analyzeTableSizes()
    {
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

        foreach ($tables as $table) {
            $size = DB::select(DB::raw("
                SELECT 
                    pg_size_pretty(pg_total_relation_size('$table')) as total_size,
                    pg_size_pretty(pg_table_size('$table')) as table_size,
                    pg_size_pretty(pg_indexes_size('$table')) as index_size,
                    (SELECT reltuples::bigint FROM pg_class WHERE relname = '$table') as row_count
            "))[0];

            $this->tableStats[$table] = [
                'total_size' => $size->total_size,
                'table_size' => $size->table_size,
                'index_size' => $size->index_size,
                'row_count' => $size->row_count
            ];
        }
    }

    private function analyzeIndexUsage()
    {
        $indexUsage = DB::select(DB::raw("
            SELECT 
                schemaname,
                tablename,
                indexname,
                idx_scan,
                idx_tup_read,
                idx_tup_fetch
            FROM pg_stat_user_indexes
            WHERE idx_scan = 0
            ORDER BY schemaname, tablename
        "));

        foreach ($indexUsage as $index) {
            if ($index->idx_scan == 0) {
                $this->missingIndexes[] = [
                    'table' => $index->tablename,
                    'index' => $index->indexname,
                    'scans' => 0
                ];
            }
        }

        // Analyze common queries that might benefit from indexes
        $this->analyzeCommonQueries();
    }

    private function analyzeCommonQueries()
    {
        // Test asset queries
        $this->benchmarkQuery(
            Asset::where('status', 'active')
                ->where('category_id', 1)
                ->orderBy('created_at', 'desc'),
            'assets_status_category_index'
        );

        // Test work order queries
        $this->benchmarkQuery(
            WorkOrder::where('status', 'open')
                ->where('priority', 'high')
                ->where('due_date', '<=', now())
                ->orderBy('due_date'),
            'work_orders_status_priority_due_date_index'
        );

        // Test maintenance schedule queries
        $this->benchmarkQuery(
            MaintenanceSchedule::where('status', 'active')
                ->where('next_due_date', '<=', now())
                ->orderBy('next_due_date'),
            'maintenance_schedules_status_next_due_date_index'
        );
    }

    private function benchmarkQuery(Builder $query, string $suggestedIndex)
    {
        $start = microtime(true);
        $result = $query->explain();
        $duration = microtime(true) - $start;

        if ($duration > 0.1) { // Queries taking more than 100ms
            $this->slowQueries[] = [
                'query' => $query->toSql(),
                'duration' => $duration,
                'suggested_index' => $suggestedIndex,
                'explain' => $result
            ];
        }
    }

    private function analyzeSlowQueries()
    {
        // Analyze slow queries from the query log
        foreach (DB::getQueryLog() as $query) {
            if ($query['time'] > 100) { // Queries taking more than 100ms
                $this->slowQueries[] = [
                    'query' => $query['query'],
                    'bindings' => $query['bindings'],
                    'time' => $query['time']
                ];
            }
        }
    }

    private function analyzeQueryPatterns()
    {
        // Analyze for N+1 queries
        $this->detectNPlusOneQueries();

        // Analyze for redundant queries
        $this->detectRedundantQueries();

        // Analyze for unoptimized joins
        $this->analyzeJoins();
    }

    private function detectNPlusOneQueries()
    {
        $queryGroups = [];
        foreach (DB::getQueryLog() as $query) {
            $pattern = preg_replace('/\d+/', 'N', $query['query']);
            if (!isset($queryGroups[$pattern])) {
                $queryGroups[$pattern] = 0;
            }
            $queryGroups[$pattern]++;
        }

        foreach ($queryGroups as $pattern => $count) {
            if ($count > 10) { // Potential N+1 problem
                $this->queryPatterns[] = [
                    'type' => 'n_plus_one',
                    'pattern' => $pattern,
                    'count' => $count,
                    'suggestion' => 'Consider using eager loading with with() method'
                ];
            }
        }
    }

    private function detectRedundantQueries()
    {
        $queryHashes = [];
        foreach (DB::getQueryLog() as $query) {
            $hash = md5($query['query'] . json_encode($query['bindings']));
            if (!isset($queryHashes[$hash])) {
                $queryHashes[$hash] = 0;
            }
            $queryHashes[$hash]++;
        }

        foreach ($queryHashes as $hash => $count) {
            if ($count > 5) { // Query repeated more than 5 times
                $this->queryPatterns[] = [
                    'type' => 'redundant',
                    'query' => $query['query'],
                    'count' => $count,
                    'suggestion' => 'Consider caching the result'
                ];
            }
        }
    }

    private function analyzeJoins()
    {
        foreach (DB::getQueryLog() as $query) {
            if (stripos($query['query'], 'join') !== false) {
                $this->analyzeJoinPerformance($query);
            }
        }
    }

    private function analyzeJoinPerformance($query)
    {
        $explain = DB::select('EXPLAIN ANALYZE ' . $query['query'], $query['bindings']);
        
        foreach ($explain as $step) {
            if (isset($step->Plan) && 
                isset($step->Plan->{'Total Cost'}) && 
                $step->Plan->{'Total Cost'} > 1000) {
                $this->queryPatterns[] = [
                    'type' => 'expensive_join',
                    'query' => $query['query'],
                    'cost' => $step->Plan->{'Total Cost'},
                    'suggestion' => 'Consider adding indexes on join columns'
                ];
            }
        }
    }

    private function checkForeignKeys()
    {
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
        
        foreach ($tables as $table) {
            $foreignKeys = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableForeignKeys($table);

            foreach ($foreignKeys as $foreignKey) {
                $this->checkForeignKeyIndex($table, $foreignKey);
            }
        }
    }

    private function checkForeignKeyIndex($table, $foreignKey)
    {
        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes($table);

        $columnNames = $foreignKey->getLocalColumns();
        $hasIndex = false;

        foreach ($indexes as $index) {
            if ($index->getColumns() === $columnNames) {
                $hasIndex = true;
                break;
            }
        }

        if (!$hasIndex) {
            $this->missingIndexes[] = [
                'table' => $table,
                'columns' => $columnNames,
                'type' => 'foreign_key',
                'suggestion' => 'Add index for foreign key columns'
            ];
        }
    }

    private function checkDataTypes()
    {
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
        
        foreach ($tables as $table) {
            $columns = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableColumns($table);

            foreach ($columns as $column) {
                $this->analyzeColumnDataType($table, $column);
            }
        }
    }

    private function analyzeColumnDataType($table, $column)
    {
        $type = $column->getType()->getName();
        $length = $column->getLength();

        // Check for oversized varchar fields
        if ($type === 'string' && $length > 255) {
            $actualMaxLength = DB::table($table)
                ->select(DB::raw("MAX(LENGTH({$column->getName()})) as max_length"))
                ->value('max_length');

            if ($actualMaxLength < 255) {
                $this->dataTypeIssues[] = [
                    'table' => $table,
                    'column' => $column->getName(),
                    'current_type' => "varchar($length)",
                    'suggested_type' => 'varchar(255)',
                    'reason' => 'Oversized varchar field'
                ];
            }
        }

        // Check for integer types
        if ($type === 'bigint') {
            $maxValue = DB::table($table)
                ->select(DB::raw("MAX({$column->getName()}) as max_value"))
                ->value('max_value');

            if ($maxValue < 2147483647) {
                $this->dataTypeIssues[] = [
                    'table' => $table,
                    'column' => $column->getName(),
                    'current_type' => 'bigint',
                    'suggested_type' => 'integer',
                    'reason' => 'Smaller integer type would suffice'
                ];
            }
        }
    }

    private function generateOptimizationReport()
    {
        $report = [
            'table_statistics' => $this->tableStats,
            'slow_queries' => $this->slowQueries,
            'missing_indexes' => $this->missingIndexes,
            'query_patterns' => $this->queryPatterns ?? [],
            'data_type_issues' => $this->dataTypeIssues ?? [],
            'recommendations' => []
        ];

        // Generate recommendations
        foreach ($this->slowQueries as $query) {
            $report['recommendations'][] = [
                'type' => 'performance',
                'issue' => 'Slow query detected',
                'query' => $query['query'],
                'suggestion' => $query['suggested_index'] ?? 'Consider adding appropriate indexes'
            ];
        }

        foreach ($this->missingIndexes as $index) {
            $report['recommendations'][] = [
                'type' => 'index',
                'issue' => 'Missing index',
                'table' => $index['table'],
                'suggestion' => "Add index {$index['suggested_index'] ?? 'on frequently queried columns'}"
            ];
        }

        // Save report
        $reportPath = storage_path('logs/db_optimization_report.json');
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));

        // Log summary
        Log::info('Database optimization report generated', [
            'slow_queries_count' => count($this->slowQueries),
            'missing_indexes_count' => count($this->missingIndexes),
            'report_path' => $reportPath
        ]);

        // Output summary to console
        $this->outputSummary($report);
    }

    private function outputSummary($report)
    {
        echo "\nDatabase Optimization Report Summary:\n";
        echo "------------------------------------\n";
        echo "Tables analyzed: " . count($report['table_statistics']) . "\n";
        echo "Slow queries found: " . count($report['slow_queries']) . "\n";
        echo "Missing indexes: " . count($report['missing_indexes']) . "\n";
        echo "Data type issues: " . count($report['data_type_issues']) . "\n";
        echo "Total recommendations: " . count($report['recommendations']) . "\n";
        echo "\nFull report saved to: " . storage_path('logs/db_optimization_report.json') . "\n";
    }
} 