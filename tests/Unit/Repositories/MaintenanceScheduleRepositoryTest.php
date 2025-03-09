<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\MaintenanceScheduleRepository;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Models\Asset;
use App\Models\MaintenanceLog;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MaintenanceScheduleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private MaintenanceScheduleRepository $maintenanceScheduleRepository;
    private User $user;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->maintenanceScheduleRepository = new MaintenanceScheduleRepository();
        $this->user = User::factory()->create();
        $this->asset = Asset::factory()->create();
    }

    /** @test */
    public function it_can_create_maintenance_schedule()
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

        $schedule = $this->maintenanceScheduleRepository->create($data);

        $this->assertInstanceOf(MaintenanceSchedule::class, $schedule);
        $this->assertEquals('Monthly Maintenance', $schedule->title);
        $this->assertEquals(30, $schedule->frequency);
    }

    /** @test */
    public function it_can_update_maintenance_schedule()
    {
        $schedule = MaintenanceSchedule::factory()->create();
        $updateData = [
            'title' => 'Updated Schedule',
            'frequency' => 45
        ];

        $updatedSchedule = $this->maintenanceScheduleRepository->update($schedule->id, $updateData);

        $this->assertEquals('Updated Schedule', $updatedSchedule->title);
        $this->assertEquals(45, $updatedSchedule->frequency);
    }

    /** @test */
    public function it_can_delete_maintenance_schedule()
    {
        $schedule = MaintenanceSchedule::factory()->create();

        $this->maintenanceScheduleRepository->delete($schedule->id);

        $this->assertSoftDeleted('maintenance_schedules', ['id' => $schedule->id]);
    }

    /** @test */
    public function it_can_find_maintenance_schedule_by_id()
    {
        $schedule = MaintenanceSchedule::factory()->create();

        $foundSchedule = $this->maintenanceScheduleRepository->find($schedule->id);

        $this->assertEquals($schedule->id, $foundSchedule->id);
    }

    /** @test */
    public function it_can_get_all_maintenance_schedules()
    {
        MaintenanceSchedule::factory()->count(3)->create();

        $schedules = $this->maintenanceScheduleRepository->all();

        $this->assertCount(3, $schedules);
    }

    /** @test */
    public function it_can_get_maintenance_schedules_by_asset()
    {
        MaintenanceSchedule::factory()->count(2)->create(['asset_id' => $this->asset->id]);
        MaintenanceSchedule::factory()->create();

        $assetSchedules = $this->maintenanceScheduleRepository->getByAsset($this->asset->id);

        $this->assertCount(2, $assetSchedules);
        $this->assertTrue($assetSchedules->every(fn ($s) => $s->asset_id === $this->asset->id));
    }

    /** @test */
    public function it_can_get_maintenance_schedules_by_status()
    {
        MaintenanceSchedule::factory()->count(2)->create(['status' => 'active']);
        MaintenanceSchedule::factory()->create(['status' => 'paused']);

        $activeSchedules = $this->maintenanceScheduleRepository->getByStatus('active');

        $this->assertCount(2, $activeSchedules);
        $this->assertTrue($activeSchedules->every(fn ($s) => $s->status === 'active'));
    }

    /** @test */
    public function it_can_get_due_maintenance_schedules()
    {
        MaintenanceSchedule::factory()->count(2)->create([
            'next_due_date' => now()->subDay(),
            'status' => 'active'
        ]);
        MaintenanceSchedule::factory()->create([
            'next_due_date' => now()->addDay()
        ]);

        $dueSchedules = $this->maintenanceScheduleRepository->getDueSchedules();

        $this->assertCount(2, $dueSchedules);
        $this->assertTrue($dueSchedules->every(fn ($s) => $s->next_due_date < now()));
    }

    /** @test */
    public function it_can_complete_maintenance()
    {
        $schedule = MaintenanceSchedule::factory()->create();
        $completionData = [
            'completed_by' => $this->user->id,
            'completion_notes' => 'Maintenance completed successfully',
            'cost' => 500
        ];

        $log = $this->maintenanceScheduleRepository->completeMaintenance($schedule->id, $completionData);

        $this->assertInstanceOf(MaintenanceLog::class, $log);
        $this->assertEquals($schedule->id, $log->maintenance_schedule_id);
        $this->assertEquals('completed', $log->status);
        $this->assertEquals(500, $log->cost);
    }

    /** @test */
    public function it_can_generate_work_order()
    {
        $schedule = MaintenanceSchedule::factory()->create([
            'title' => 'Monthly Maintenance',
            'assigned_to' => $this->user->id
        ]);

        $workOrder = $this->maintenanceScheduleRepository->generateWorkOrder($schedule->id);

        $this->assertInstanceOf(WorkOrder::class, $workOrder);
        $this->assertEquals($schedule->asset_id, $workOrder->asset_id);
        $this->assertEquals($schedule->assigned_to, $workOrder->assigned_to);
        $this->assertEquals('preventive', $workOrder->type);
    }

    /** @test */
    public function it_can_get_maintenance_history()
    {
        $schedule = MaintenanceSchedule::factory()->create();
        MaintenanceLog::factory()->count(3)->create([
            'maintenance_schedule_id' => $schedule->id
        ]);

        $history = $this->maintenanceScheduleRepository->getMaintenanceHistory($schedule->id);

        $this->assertCount(3, $history);
        $this->assertTrue($history->every(fn ($log) => $log->maintenance_schedule_id === $schedule->id));
    }

    /** @test */
    public function it_can_calculate_average_completion_time()
    {
        $schedule = MaintenanceSchedule::factory()->create();
        
        MaintenanceLog::factory()->create([
            'maintenance_schedule_id' => $schedule->id,
            'started_at' => now()->subHours(5),
            'completed_at' => now()
        ]);

        MaintenanceLog::factory()->create([
            'maintenance_schedule_id' => $schedule->id,
            'started_at' => now()->subHours(7),
            'completed_at' => now()
        ]);

        $averageTime = $this->maintenanceScheduleRepository->getAverageCompletionTime($schedule->id);

        $this->assertEquals(6, $averageTime);
    }

    /** @test */
    public function it_can_calculate_total_maintenance_cost()
    {
        $schedule = MaintenanceSchedule::factory()->create();
        
        MaintenanceLog::factory()->create([
            'maintenance_schedule_id' => $schedule->id,
            'cost' => 500
        ]);

        MaintenanceLog::factory()->create([
            'maintenance_schedule_id' => $schedule->id,
            'cost' => 700
        ]);

        $totalCost = $this->maintenanceScheduleRepository->getTotalMaintenanceCost($schedule->id);

        $this->assertEquals(1200, $totalCost);
    }

    /** @test */
    public function it_can_get_maintenance_statistics()
    {
        $schedule = MaintenanceSchedule::factory()->create();
        
        MaintenanceLog::factory()->count(2)->create([
            'maintenance_schedule_id' => $schedule->id,
            'status' => 'completed'
        ]);

        MaintenanceLog::factory()->create([
            'maintenance_schedule_id' => $schedule->id,
            'status' => 'missed'
        ]);

        $stats = $this->maintenanceScheduleRepository->getStatistics($schedule->id);

        $this->assertEquals(3, $stats['total_logs']);
        $this->assertEquals(2, $stats['completed_count']);
        $this->assertEquals(1, $stats['missed_count']);
        $this->assertEquals(66.67, round($stats['completion_rate'], 2));
    }

    /** @test */
    public function it_can_search_maintenance_schedules()
    {
        MaintenanceSchedule::factory()->create(['title' => 'Monthly HVAC Maintenance']);
        MaintenanceSchedule::factory()->create(['title' => 'Quarterly HVAC Service']);
        MaintenanceSchedule::factory()->create(['title' => 'Annual Inspection']);

        $searchResults = $this->maintenanceScheduleRepository->search('HVAC');

        $this->assertCount(2, $searchResults);
        $this->assertTrue($searchResults->contains('title', 'Monthly HVAC Maintenance'));
        $this->assertTrue($searchResults->contains('title', 'Quarterly HVAC Service'));
    }
} 