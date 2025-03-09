<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\MaintenanceScheduleService;
use App\Repositories\MaintenanceScheduleRepository;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Models\Asset;
use App\Models\MaintenanceLog;
use App\Events\MaintenanceScheduleCreated;
use App\Events\MaintenanceScheduleDue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;

class MaintenanceScheduleServiceTest extends TestCase
{
    use RefreshDatabase;

    private MaintenanceScheduleService $maintenanceScheduleService;
    private $maintenanceScheduleRepository;
    private User $user;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the repository
        $this->maintenanceScheduleRepository = Mockery::mock(MaintenanceScheduleRepository::class);
        
        // Create the service with mocked repository
        $this->maintenanceScheduleService = new MaintenanceScheduleService($this->maintenanceScheduleRepository);

        // Create test data
        $this->user = User::factory()->create();
        $this->asset = Asset::factory()->create();

        // Fake events
        Event::fake([
            MaintenanceScheduleCreated::class,
            MaintenanceScheduleDue::class
        ]);
    }

    /** @test */
    public function it_creates_maintenance_schedule_and_dispatches_event()
    {
        $data = [
            'asset_id' => $this->asset->id,
            'title' => 'Monthly Maintenance',
            'description' => 'Regular monthly maintenance check',
            'frequency' => 30,
            'frequency_unit' => 'days',
            'next_due_date' => now()->addDays(30)->toDateString(),
            'assigned_to' => $this->user->id,
            'status' => 'active'
        ];

        $schedule = MaintenanceSchedule::factory()->make($data);

        $this->maintenanceScheduleRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($schedule);

        $result = $this->maintenanceScheduleService->createSchedule($data);

        $this->assertEquals($schedule, $result);
        Event::assertDispatched(MaintenanceScheduleCreated::class);
    }

    /** @test */
    public function it_updates_maintenance_schedule()
    {
        $schedule = MaintenanceSchedule::factory()->create();
        $updateData = [
            'frequency' => 45,
            'status' => 'paused'
        ];

        $this->maintenanceScheduleRepository
            ->shouldReceive('update')
            ->once()
            ->with($schedule->id, $updateData)
            ->andReturn($schedule);

        $result = $this->maintenanceScheduleService->updateSchedule($schedule->id, $updateData);

        $this->assertEquals($schedule, $result);
    }

    /** @test */
    public function it_completes_maintenance()
    {
        $schedule = MaintenanceSchedule::factory()->create([
            'status' => 'active',
            'frequency' => 30
        ]);

        $completionData = [
            'completed_by' => $this->user->id,
            'completion_notes' => 'Maintenance completed successfully',
            'cost' => 500
        ];

        $this->maintenanceScheduleRepository
            ->shouldReceive('completeMaintenance')
            ->once()
            ->with($schedule->id, $completionData)
            ->andReturn($schedule);

        $this->maintenanceScheduleRepository
            ->shouldReceive('update')
            ->once()
            ->with($schedule->id, [
                'last_completed_at' => Mockery::any(),
                'next_due_date' => Mockery::any()
            ])
            ->andReturn($schedule);

        $result = $this->maintenanceScheduleService->completeMaintenance($schedule->id, $completionData);

        $this->assertEquals($schedule, $result);
    }

    /** @test */
    public function it_checks_for_due_maintenance()
    {
        $dueSchedules = collect([
            MaintenanceSchedule::factory()->create([
                'next_due_date' => now()->subDay()
            ])
        ]);

        $this->maintenanceScheduleRepository
            ->shouldReceive('getDueSchedules')
            ->once()
            ->andReturn($dueSchedules);

        $result = $this->maintenanceScheduleService->checkDueSchedules();

        $this->assertEquals($dueSchedules, $result);
        Event::assertDispatched(MaintenanceScheduleDue::class);
    }

    /** @test */
    public function it_generates_work_order()
    {
        $schedule = MaintenanceSchedule::factory()->create();

        $this->maintenanceScheduleRepository
            ->shouldReceive('generateWorkOrder')
            ->once()
            ->with($schedule->id)
            ->andReturn($schedule);

        $result = $this->maintenanceScheduleService->generateWorkOrder($schedule->id);

        $this->assertEquals($schedule, $result);
    }

    /** @test */
    public function it_calculates_completion_rate()
    {
        $schedule = MaintenanceSchedule::factory()->create();
        
        // Create maintenance logs
        MaintenanceLog::factory()->count(2)->create([
            'maintenance_schedule_id' => $schedule->id,
            'status' => 'completed'
        ]);

        MaintenanceLog::factory()->create([
            'maintenance_schedule_id' => $schedule->id,
            'status' => 'missed'
        ]);

        $this->maintenanceScheduleRepository
            ->shouldReceive('find')
            ->once()
            ->with($schedule->id)
            ->andReturn($schedule);

        $completionRate = $this->maintenanceScheduleService->calculateCompletionRate($schedule->id);

        $this->assertEquals(66.67, round($completionRate, 2));
    }

    /** @test */
    public function it_calculates_average_completion_time()
    {
        $schedule = MaintenanceSchedule::factory()->create();

        $this->maintenanceScheduleRepository
            ->shouldReceive('find')
            ->once()
            ->with($schedule->id)
            ->andReturn($schedule);

        $this->maintenanceScheduleRepository
            ->shouldReceive('getAverageCompletionTime')
            ->once()
            ->with($schedule->id)
            ->andReturn(5);

        $averageTime = $this->maintenanceScheduleService->calculateAverageCompletionTime($schedule->id);

        $this->assertEquals(5, $averageTime);
    }

    /** @test */
    public function it_calculates_total_maintenance_cost()
    {
        $schedule = MaintenanceSchedule::factory()->create();

        $this->maintenanceScheduleRepository
            ->shouldReceive('find')
            ->once()
            ->with($schedule->id)
            ->andReturn($schedule);

        $this->maintenanceScheduleRepository
            ->shouldReceive('getTotalMaintenanceCost')
            ->once()
            ->with($schedule->id)
            ->andReturn(1500);

        $totalCost = $this->maintenanceScheduleService->calculateTotalMaintenanceCost($schedule->id);

        $this->assertEquals(1500, $totalCost);
    }

    /** @test */
    public function it_gets_maintenance_history()
    {
        $schedule = MaintenanceSchedule::factory()->create();
        $history = collect([
            MaintenanceLog::factory()->create([
                'maintenance_schedule_id' => $schedule->id
            ])
        ]);

        $this->maintenanceScheduleRepository
            ->shouldReceive('find')
            ->once()
            ->with($schedule->id)
            ->andReturn($schedule);

        $this->maintenanceScheduleRepository
            ->shouldReceive('getMaintenanceHistory')
            ->once()
            ->with($schedule->id)
            ->andReturn($history);

        $result = $this->maintenanceScheduleService->getMaintenanceHistory($schedule->id);

        $this->assertEquals($history, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 