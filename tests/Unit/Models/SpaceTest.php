<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Space;
use App\Models\Floor;
use App\Models\Building;
use App\Models\Asset;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;

class SpaceTest extends TestCase
{
    use RefreshDatabase;

    private Space $space;
    private Floor $floor;
    private Building $building;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test building, floor and space
        $this->building = Building::factory()->create();
        $this->floor = Floor::factory()->create([
            'building_id' => $this->building->id
        ]);
        $this->space = Space::factory()->create([
            'floor_id' => $this->floor->id
        ]);
    }

    /** @test */
    public function it_belongs_to_a_floor()
    {
        $this->assertInstanceOf(Floor::class, $this->space->floor);
        $this->assertEquals($this->floor->id, $this->space->floor->id);
    }

    /** @test */
    public function it_belongs_to_a_building_through_floor()
    {
        $this->assertInstanceOf(Building::class, $this->space->building);
        $this->assertEquals($this->building->id, $this->space->building->id);
    }

    /** @test */
    public function it_has_many_assets()
    {
        Asset::factory()->count(3)->create([
            'space_id' => $this->space->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->space->assets);
        $this->assertCount(3, $this->space->assets);
        $this->assertInstanceOf(Asset::class, $this->space->assets->first());
    }

    /** @test */
    public function it_has_many_work_orders()
    {
        WorkOrder::factory()->count(3)->create([
            'space_id' => $this->space->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->space->workOrders);
        $this->assertCount(3, $this->space->workOrders);
        $this->assertInstanceOf(WorkOrder::class, $this->space->workOrders->first());
    }

    /** @test */
    public function it_can_determine_if_occupied()
    {
        $this->space->update(['status' => 'occupied']);
        $this->assertTrue($this->space->isOccupied());

        $this->space->update(['status' => 'vacant']);
        $this->assertFalse($this->space->isOccupied());
    }

    /** @test */
    public function it_can_determine_if_vacant()
    {
        $this->space->update(['status' => 'vacant']);
        $this->assertTrue($this->space->isVacant());

        $this->space->update(['status' => 'occupied']);
        $this->assertFalse($this->space->isVacant());
    }

    /** @test */
    public function it_can_get_active_work_orders()
    {
        // Create both active and completed work orders
        WorkOrder::factory()->count(2)->create([
            'space_id' => $this->space->id,
            'status' => 'in_progress'
        ]);

        WorkOrder::factory()->create([
            'space_id' => $this->space->id,
            'status' => 'completed'
        ]);

        $activeWorkOrders = $this->space->activeWorkOrders();
        
        $this->assertCount(2, $activeWorkOrders);
        $this->assertTrue($activeWorkOrders->every(fn ($workOrder) => $workOrder->status === 'in_progress'));
    }

    /** @test */
    public function it_can_get_asset_count_by_category()
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Asset::factory()->count(2)->create([
            'space_id' => $this->space->id,
            'category_id' => $category1->id
        ]);

        Asset::factory()->create([
            'space_id' => $this->space->id,
            'category_id' => $category2->id
        ]);

        $assetCounts = $this->space->assetCountByCategory();

        $this->assertEquals(2, $assetCounts[$category1->name]);
        $this->assertEquals(1, $assetCounts[$category2->name]);
    }

    /** @test */
    public function it_can_get_total_maintenance_cost()
    {
        // Create work orders with costs
        WorkOrder::factory()->count(3)->create([
            'space_id' => $this->space->id,
            'cost' => 1000
        ]);

        $this->assertEquals(3000, $this->space->totalMaintenanceCost());
    }

    /** @test */
    public function it_can_get_occupancy_history()
    {
        $this->space->update(['status' => 'occupied']);
        $this->space->occupancyHistory()->create([
            'status' => 'occupied',
            'start_date' => now()->subDays(30),
            'end_date' => now()->subDays(15)
        ]);

        $this->space->occupancyHistory()->create([
            'status' => 'vacant',
            'start_date' => now()->subDays(15),
            'end_date' => now()
        ]);

        $history = $this->space->occupancyHistory;
        
        $this->assertCount(2, $history);
        $this->assertEquals('occupied', $history->first()->status);
        $this->assertEquals('vacant', $history->last()->status);
    }

    /** @test */
    public function it_can_calculate_occupancy_rate()
    {
        // Create occupancy history spanning 100 days
        // 60 days occupied, 40 days vacant
        $this->space->occupancyHistory()->create([
            'status' => 'occupied',
            'start_date' => now()->subDays(100),
            'end_date' => now()->subDays(40)
        ]);

        $this->space->occupancyHistory()->create([
            'status' => 'vacant',
            'start_date' => now()->subDays(40),
            'end_date' => now()
        ]);

        $this->assertEquals(60, $this->space->calculateOccupancyRate());
    }

    /** @test */
    public function it_can_get_current_occupant()
    {
        $occupant = User::factory()->create();
        
        $this->space->update([
            'status' => 'occupied',
            'occupant_id' => $occupant->id
        ]);

        $this->assertInstanceOf(User::class, $this->space->currentOccupant);
        $this->assertEquals($occupant->id, $this->space->currentOccupant->id);
    }
} 