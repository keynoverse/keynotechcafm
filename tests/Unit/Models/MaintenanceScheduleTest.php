<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\MaintenanceSchedule;
use App\Models\Asset;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Space;
use App\Models\User;
use App\Models\MaintenanceLog;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;

class MaintenanceScheduleTest extends TestCase
{
    use RefreshDatabase;

    private MaintenanceSchedule $maintenanceSchedule;
    private Asset $asset;
    private User $assignedUser;
    private Building $building;
    private Floor $floor;
    private Space $space;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->assignedUser = User::factory()->create();
        $this->building = Building::factory()->create();
        $this->floor = Floor::factory()->create([
            'building_id' => $this->building->id
        ]);
        $this->space = Space::factory()->create([
            'floor_id' => $this->floor->id
        ]);
        $this->asset = Asset::factory()->create([
            'building_id' => $this->building->id,
            'floor_id' => $this->floor->id,
            'space_id' => $this->space->id
        ]);

        // Create test maintenance schedule
        $this->maintenanceSchedule = MaintenanceSchedule::factory()->create([
            'asset_id' => $this->asset->id,
            'assigned_to' => $this->assignedUser->id
        ]);
    }

    /** @test */
    public function it_belongs_to_an_asset()
    {
        $this->assertInstanceOf(Asset::class, $this->maintenanceSchedule->asset);
        $this->assertEquals($this->asset->id, $this->maintenanceSchedule->asset->id);
    }

    /** @test */
    public function it_belongs_to_an_assigned_user()
    {
        $this->assertInstanceOf(User::class, $this->maintenanceSchedule->assignedTo);
        $this->assertEquals($this->assignedUser->id, $this->maintenanceSchedule->assignedTo->id);
    }

    /** @test */
    public function it_has_many_maintenance_logs()
    {
        MaintenanceLog::factory()->count(3)->create([
            'maintenance_schedule_id' => $this->maintenanceSchedule->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->maintenanceSchedule->maintenanceLogs);
        $this->assertCount(3, $this->maintenanceSchedule->maintenanceLogs);
        $this->assertInstanceOf(MaintenanceLog::class, $this->maintenanceSchedule->maintenanceLogs->first());
    }

    /** @test */
    public function it_has_many_work_orders()
    {
        WorkOrder::factory()->count(3)->create([
            'maintenance_schedule_id' => $this->maintenanceSchedule->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->maintenanceSchedule->workOrders);
        $this->assertCount(3, $this->maintenanceSchedule->workOrders);
        $this->assertInstanceOf(WorkOrder::class, $this->maintenanceSchedule->workOrders->first());
    }

    /** @test */
    public function it_can_determine_if_maintenance_is_due()
    {
        $this->maintenanceSchedule->update([
            'next_due_date' => now()->subDay()
        ]);
        $this->assertTrue($this->maintenanceSchedule->isMaintenanceDue());

        $this->maintenanceSchedule->update([
            'next_due_date' => now()->addDay()
        ]);
        $this->assertFalse($this->maintenanceSchedule->isMaintenanceDue());
    }

    /** @test */
    public function it_can_calculate_next_due_date()
    {
        $this->maintenanceSchedule->update([
            'frequency' => 30, // 30 days
            'last_completed_at' => now()
        ]);

        $expectedDate = now()->addDays(30)->startOfDay();
        $this->assertEquals($expectedDate, $this->maintenanceSchedule->calculateNextDueDate()->startOfDay());
    }

    /** @test */
    public function it_can_track_completion_status()
    {
        $this->maintenanceSchedule->update([
            'last_completed_at' => now(),
            'completion_status' => 'completed'
        ]);

        $this->assertTrue($this->maintenanceSchedule->isCompleted());
        $this->assertFalse($this->maintenanceSchedule->isPending());
    }

    /** @test */
    public function it_can_calculate_completion_rate()
    {
        // Create maintenance logs for the past 3 months
        // 2 completed, 1 missed
        MaintenanceLog::factory()->create([
            'maintenance_schedule_id' => $this->maintenanceSchedule->id,
            'status' => 'completed',
            'completed_at' => now()->subMonths(2)
        ]);

        MaintenanceLog::factory()->create([
            'maintenance_schedule_id' => $this->maintenanceSchedule->id,
            'status' => 'completed',
            'completed_at' => now()->subMonth()
        ]);

        MaintenanceLog::factory()->create([
            'maintenance_schedule_id' => $this->maintenanceSchedule->id,
            'status' => 'missed',
            'completed_at' => null
        ]);

        $this->assertEquals(66.67, round($this->maintenanceSchedule->calculateCompletionRate(), 2));
    }

    /** @test */
    public function it_can_get_maintenance_history()
    {
        MaintenanceLog::factory()->count(3)->create([
            'maintenance_schedule_id' => $this->maintenanceSchedule->id
        ]);

        $history = $this->maintenanceSchedule->maintenanceHistory();
        
        $this->assertCount(3, $history);
        $this->assertInstanceOf(MaintenanceLog::class, $history->first());
    }

    /** @test */
    public function it_can_calculate_average_completion_time()
    {
        MaintenanceLog::factory()->create([
            'maintenance_schedule_id' => $this->maintenanceSchedule->id,
            'started_at' => now()->subHours(5),
            'completed_at' => now()
        ]);

        MaintenanceLog::factory()->create([
            'maintenance_schedule_id' => $this->maintenanceSchedule->id,
            'started_at' => now()->subHours(7),
            'completed_at' => now()
        ]);

        $this->assertEquals(6, $this->maintenanceSchedule->calculateAverageCompletionTime());
    }

    /** @test */
    public function it_can_calculate_total_maintenance_cost()
    {
        MaintenanceLog::factory()->create([
            'maintenance_schedule_id' => $this->maintenanceSchedule->id,
            'cost' => 500
        ]);

        MaintenanceLog::factory()->create([
            'maintenance_schedule_id' => $this->maintenanceSchedule->id,
            'cost' => 700
        ]);

        $this->assertEquals(1200, $this->maintenanceSchedule->calculateTotalMaintenanceCost());
    }

    /** @test */
    public function it_can_generate_work_order()
    {
        $workOrder = $this->maintenanceSchedule->generateWorkOrder();

        $this->assertInstanceOf(WorkOrder::class, $workOrder);
        $this->assertEquals($this->maintenanceSchedule->asset_id, $workOrder->asset_id);
        $this->assertEquals($this->maintenanceSchedule->assigned_to, $workOrder->assigned_to);
        $this->assertEquals('preventive', $workOrder->type);
    }
} 