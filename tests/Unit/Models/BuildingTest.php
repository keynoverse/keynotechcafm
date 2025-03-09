<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Space;
use App\Models\Asset;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;

class BuildingTest extends TestCase
{
    use RefreshDatabase;

    private Building $building;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test building
        $this->building = Building::factory()->create();
    }

    /** @test */
    public function it_has_many_floors()
    {
        Floor::factory()->count(3)->create([
            'building_id' => $this->building->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->building->floors);
        $this->assertCount(3, $this->building->floors);
        $this->assertInstanceOf(Floor::class, $this->building->floors->first());
    }

    /** @test */
    public function it_has_many_spaces_through_floors()
    {
        $floor = Floor::factory()->create([
            'building_id' => $this->building->id
        ]);

        Space::factory()->count(3)->create([
            'floor_id' => $floor->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->building->spaces);
        $this->assertCount(3, $this->building->spaces);
        $this->assertInstanceOf(Space::class, $this->building->spaces->first());
    }

    /** @test */
    public function it_has_many_assets()
    {
        Asset::factory()->count(3)->create([
            'building_id' => $this->building->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->building->assets);
        $this->assertCount(3, $this->building->assets);
        $this->assertInstanceOf(Asset::class, $this->building->assets->first());
    }

    /** @test */
    public function it_has_many_work_orders()
    {
        WorkOrder::factory()->count(3)->create([
            'building_id' => $this->building->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->building->workOrders);
        $this->assertCount(3, $this->building->workOrders);
        $this->assertInstanceOf(WorkOrder::class, $this->building->workOrders->first());
    }

    /** @test */
    public function it_can_calculate_total_area()
    {
        $floor1 = Floor::factory()->create([
            'building_id' => $this->building->id,
            'total_area' => 1000
        ]);

        $floor2 = Floor::factory()->create([
            'building_id' => $this->building->id,
            'total_area' => 1500
        ]);

        $this->assertEquals(2500, $this->building->totalArea());
    }

    /** @test */
    public function it_can_calculate_occupancy_rate()
    {
        $floor = Floor::factory()->create([
            'building_id' => $this->building->id,
            'total_area' => 1000
        ]);

        Space::factory()->create([
            'floor_id' => $floor->id,
            'area' => 600,
            'status' => 'occupied'
        ]);

        Space::factory()->create([
            'floor_id' => $floor->id,
            'area' => 400,
            'status' => 'vacant'
        ]);

        $this->assertEquals(60, $this->building->occupancyRate());
    }

    /** @test */
    public function it_can_get_active_work_orders()
    {
        // Create both active and completed work orders
        WorkOrder::factory()->count(2)->create([
            'building_id' => $this->building->id,
            'status' => 'in_progress'
        ]);

        WorkOrder::factory()->create([
            'building_id' => $this->building->id,
            'status' => 'completed'
        ]);

        $activeWorkOrders = $this->building->activeWorkOrders();
        
        $this->assertCount(2, $activeWorkOrders);
        $this->assertTrue($activeWorkOrders->every(fn ($workOrder) => $workOrder->status === 'in_progress'));
    }

    /** @test */
    public function it_can_get_maintenance_statistics()
    {
        // Create work orders with different types
        WorkOrder::factory()->count(2)->create([
            'building_id' => $this->building->id,
            'type' => 'preventive'
        ]);

        WorkOrder::factory()->create([
            'building_id' => $this->building->id,
            'type' => 'corrective'
        ]);

        $stats = $this->building->maintenanceStatistics();

        $this->assertEquals(2, $stats['preventive']);
        $this->assertEquals(1, $stats['corrective']);
        $this->assertEquals(3, $stats['total']);
    }

    /** @test */
    public function it_can_get_asset_count_by_category()
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Asset::factory()->count(2)->create([
            'building_id' => $this->building->id,
            'category_id' => $category1->id
        ]);

        Asset::factory()->create([
            'building_id' => $this->building->id,
            'category_id' => $category2->id
        ]);

        $assetCounts = $this->building->assetCountByCategory();

        $this->assertEquals(2, $assetCounts[$category1->name]);
        $this->assertEquals(1, $assetCounts[$category2->name]);
    }

    /** @test */
    public function it_can_get_total_maintenance_cost()
    {
        // Create work orders with costs
        WorkOrder::factory()->count(3)->create([
            'building_id' => $this->building->id,
            'cost' => 1000
        ]);

        $this->assertEquals(3000, $this->building->totalMaintenanceCost());
    }
} 