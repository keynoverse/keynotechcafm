<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Asset;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Space;
use App\Models\Category;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceLog;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;

class AssetTest extends TestCase
{
    use RefreshDatabase;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test asset
        $this->asset = Asset::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_category()
    {
        $category = Category::factory()->create();
        $this->asset->category()->associate($category);
        $this->asset->save();

        $this->assertInstanceOf(Category::class, $this->asset->category);
        $this->assertEquals($category->id, $this->asset->category->id);
    }

    /** @test */
    public function it_can_be_assigned_to_a_building()
    {
        $building = Building::factory()->create();
        $this->asset->building()->associate($building);
        $this->asset->save();

        $this->assertInstanceOf(Building::class, $this->asset->building);
        $this->assertEquals($building->id, $this->asset->building->id);
    }

    /** @test */
    public function it_can_be_assigned_to_a_floor()
    {
        $building = Building::factory()->create();
        $floor = Floor::factory()->create(['building_id' => $building->id]);
        
        $this->asset->floor()->associate($floor);
        $this->asset->save();

        $this->assertInstanceOf(Floor::class, $this->asset->floor);
        $this->assertEquals($floor->id, $this->asset->floor->id);
    }

    /** @test */
    public function it_can_be_assigned_to_a_space()
    {
        $building = Building::factory()->create();
        $floor = Floor::factory()->create(['building_id' => $building->id]);
        $space = Space::factory()->create(['floor_id' => $floor->id]);
        
        $this->asset->space()->associate($space);
        $this->asset->save();

        $this->assertInstanceOf(Space::class, $this->asset->space);
        $this->assertEquals($space->id, $this->asset->space->id);
    }

    /** @test */
    public function it_has_many_maintenance_schedules()
    {
        MaintenanceSchedule::factory()->count(3)->create([
            'asset_id' => $this->asset->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->asset->maintenanceSchedules);
        $this->assertCount(3, $this->asset->maintenanceSchedules);
        $this->assertInstanceOf(MaintenanceSchedule::class, $this->asset->maintenanceSchedules->first());
    }

    /** @test */
    public function it_has_many_maintenance_logs()
    {
        MaintenanceLog::factory()->count(3)->create([
            'asset_id' => $this->asset->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->asset->maintenanceLogs);
        $this->assertCount(3, $this->asset->maintenanceLogs);
        $this->assertInstanceOf(MaintenanceLog::class, $this->asset->maintenanceLogs->first());
    }

    /** @test */
    public function it_has_many_work_orders()
    {
        WorkOrder::factory()->count(3)->create([
            'asset_id' => $this->asset->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->asset->workOrders);
        $this->assertCount(3, $this->asset->workOrders);
        $this->assertInstanceOf(WorkOrder::class, $this->asset->workOrders->first());
    }

    /** @test */
    public function it_can_calculate_maintenance_cost()
    {
        // Create maintenance logs with costs
        MaintenanceLog::factory()->count(3)->create([
            'asset_id' => $this->asset->id,
            'cost' => 100
        ]);

        $this->assertEquals(300, $this->asset->totalMaintenanceCost());
    }

    /** @test */
    public function it_can_determine_if_maintenance_is_due()
    {
        // Create a maintenance schedule due today
        MaintenanceSchedule::factory()->create([
            'asset_id' => $this->asset->id,
            'next_due_date' => now()
        ]);

        $this->assertTrue($this->asset->isMaintenanceDue());

        // Create a maintenance schedule due in the future
        $this->asset->maintenanceSchedules()->delete();
        MaintenanceSchedule::factory()->create([
            'asset_id' => $this->asset->id,
            'next_due_date' => now()->addDays(7)
        ]);

        $this->assertFalse($this->asset->isMaintenanceDue());
    }

    /** @test */
    public function it_can_get_active_work_orders()
    {
        // Create both active and completed work orders
        WorkOrder::factory()->count(2)->create([
            'asset_id' => $this->asset->id,
            'status' => 'in_progress'
        ]);

        WorkOrder::factory()->create([
            'asset_id' => $this->asset->id,
            'status' => 'completed'
        ]);

        $activeWorkOrders = $this->asset->activeWorkOrders();
        
        $this->assertCount(2, $activeWorkOrders);
        $this->assertTrue($activeWorkOrders->every(fn ($workOrder) => $workOrder->status === 'in_progress'));
    }

    /** @test */
    public function it_can_track_warranty_status()
    {
        // Asset under warranty
        $this->asset->update([
            'warranty_expiry' => now()->addYear()
        ]);
        
        $this->assertTrue($this->asset->isUnderWarranty());

        // Asset with expired warranty
        $this->asset->update([
            'warranty_expiry' => now()->subDay()
        ]);
        
        $this->assertFalse($this->asset->isUnderWarranty());
    }

    /** @test */
    public function it_can_calculate_age()
    {
        $this->asset->update([
            'purchase_date' => now()->subYears(2)
        ]);

        $this->assertEquals(2, $this->asset->age());
    }
} 