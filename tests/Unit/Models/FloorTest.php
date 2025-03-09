<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Floor;
use App\Models\Building;
use App\Models\Space;
use App\Models\Asset;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;

class FloorTest extends TestCase
{
    use RefreshDatabase;

    private Floor $floor;
    private Building $building;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test building and floor
        $this->building = Building::factory()->create();
        $this->floor = Floor::factory()->create([
            'building_id' => $this->building->id
        ]);
    }

    /** @test */
    public function it_belongs_to_a_building()
    {
        $this->assertInstanceOf(Building::class, $this->floor->building);
        $this->assertEquals($this->building->id, $this->floor->building->id);
    }

    /** @test */
    public function it_has_many_spaces()
    {
        Space::factory()->count(3)->create([
            'floor_id' => $this->floor->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->floor->spaces);
        $this->assertCount(3, $this->floor->spaces);
        $this->assertInstanceOf(Space::class, $this->floor->spaces->first());
    }

    /** @test */
    public function it_has_many_assets()
    {
        Asset::factory()->count(3)->create([
            'floor_id' => $this->floor->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->floor->assets);
        $this->assertCount(3, $this->floor->assets);
        $this->assertInstanceOf(Asset::class, $this->floor->assets->first());
    }

    /** @test */
    public function it_has_many_work_orders()
    {
        WorkOrder::factory()->count(3)->create([
            'floor_id' => $this->floor->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->floor->workOrders);
        $this->assertCount(3, $this->floor->workOrders);
        $this->assertInstanceOf(WorkOrder::class, $this->floor->workOrders->first());
    }

    /** @test */
    public function it_can_calculate_occupied_area()
    {
        Space::factory()->create([
            'floor_id' => $this->floor->id,
            'area' => 500,
            'status' => 'occupied'
        ]);

        Space::factory()->create([
            'floor_id' => $this->floor->id,
            'area' => 300,
            'status' => 'occupied'
        ]);

        Space::factory()->create([
            'floor_id' => $this->floor->id,
            'area' => 200,
            'status' => 'vacant'
        ]);

        $this->assertEquals(800, $this->floor->occupiedArea());
    }

    /** @test */
    public function it_can_calculate_vacant_area()
    {
        Space::factory()->create([
            'floor_id' => $this->floor->id,
            'area' => 500,
            'status' => 'occupied'
        ]);

        Space::factory()->create([
            'floor_id' => $this->floor->id,
            'area' => 200,
            'status' => 'vacant'
        ]);

        $this->assertEquals(200, $this->floor->vacantArea());
    }

    /** @test */
    public function it_can_calculate_occupancy_rate()
    {
        Space::factory()->create([
            'floor_id' => $this->floor->id,
            'area' => 600,
            'status' => 'occupied'
        ]);

        Space::factory()->create([
            'floor_id' => $this->floor->id,
            'area' => 400,
            'status' => 'vacant'
        ]);

        $this->assertEquals(60, $this->floor->occupancyRate());
    }

    /** @test */
    public function it_can_get_active_work_orders()
    {
        // Create both active and completed work orders
        WorkOrder::factory()->count(2)->create([
            'floor_id' => $this->floor->id,
            'status' => 'in_progress'
        ]);

        WorkOrder::factory()->create([
            'floor_id' => $this->floor->id,
            'status' => 'completed'
        ]);

        $activeWorkOrders = $this->floor->activeWorkOrders();
        
        $this->assertCount(2, $activeWorkOrders);
        $this->assertTrue($activeWorkOrders->every(fn ($workOrder) => $workOrder->status === 'in_progress'));
    }

    /** @test */
    public function it_can_get_space_count_by_type()
    {
        Space::factory()->count(2)->create([
            'floor_id' => $this->floor->id,
            'type' => 'office'
        ]);

        Space::factory()->create([
            'floor_id' => $this->floor->id,
            'type' => 'meeting_room'
        ]);

        $spaceCounts = $this->floor->spaceCountByType();

        $this->assertEquals(2, $spaceCounts['office']);
        $this->assertEquals(1, $spaceCounts['meeting_room']);
    }

    /** @test */
    public function it_can_get_asset_count_by_category()
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Asset::factory()->count(2)->create([
            'floor_id' => $this->floor->id,
            'category_id' => $category1->id
        ]);

        Asset::factory()->create([
            'floor_id' => $this->floor->id,
            'category_id' => $category2->id
        ]);

        $assetCounts = $this->floor->assetCountByCategory();

        $this->assertEquals(2, $assetCounts[$category1->name]);
        $this->assertEquals(1, $assetCounts[$category2->name]);
    }

    /** @test */
    public function it_can_get_total_maintenance_cost()
    {
        // Create work orders with costs
        WorkOrder::factory()->count(3)->create([
            'floor_id' => $this->floor->id,
            'cost' => 1000
        ]);

        $this->assertEquals(3000, $this->floor->totalMaintenanceCost());
    }
} 