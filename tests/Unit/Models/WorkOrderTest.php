<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\Asset;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Space;
use App\Models\Comment;
use App\Models\Attachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;

class WorkOrderTest extends TestCase
{
    use RefreshDatabase;

    private WorkOrder $workOrder;
    private User $assignedUser;
    private Asset $asset;
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

        // Create test work order
        $this->workOrder = WorkOrder::factory()->create([
            'assigned_to' => $this->assignedUser->id,
            'asset_id' => $this->asset->id,
            'building_id' => $this->building->id,
            'floor_id' => $this->floor->id,
            'space_id' => $this->space->id
        ]);
    }

    /** @test */
    public function it_belongs_to_an_assigned_user()
    {
        $this->assertInstanceOf(User::class, $this->workOrder->assignedTo);
        $this->assertEquals($this->assignedUser->id, $this->workOrder->assignedTo->id);
    }

    /** @test */
    public function it_belongs_to_an_asset()
    {
        $this->assertInstanceOf(Asset::class, $this->workOrder->asset);
        $this->assertEquals($this->asset->id, $this->workOrder->asset->id);
    }

    /** @test */
    public function it_belongs_to_a_building()
    {
        $this->assertInstanceOf(Building::class, $this->workOrder->building);
        $this->assertEquals($this->building->id, $this->workOrder->building->id);
    }

    /** @test */
    public function it_belongs_to_a_floor()
    {
        $this->assertInstanceOf(Floor::class, $this->workOrder->floor);
        $this->assertEquals($this->floor->id, $this->workOrder->floor->id);
    }

    /** @test */
    public function it_belongs_to_a_space()
    {
        $this->assertInstanceOf(Space::class, $this->workOrder->space);
        $this->assertEquals($this->space->id, $this->workOrder->space->id);
    }

    /** @test */
    public function it_has_many_comments()
    {
        Comment::factory()->count(3)->create([
            'work_order_id' => $this->workOrder->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->workOrder->comments);
        $this->assertCount(3, $this->workOrder->comments);
        $this->assertInstanceOf(Comment::class, $this->workOrder->comments->first());
    }

    /** @test */
    public function it_has_many_attachments()
    {
        Attachment::factory()->count(3)->create([
            'work_order_id' => $this->workOrder->id
        ]);

        $this->assertInstanceOf(Collection::class, $this->workOrder->attachments);
        $this->assertCount(3, $this->workOrder->attachments);
        $this->assertInstanceOf(Attachment::class, $this->workOrder->attachments->first());
    }

    /** @test */
    public function it_can_determine_if_overdue()
    {
        $this->workOrder->update([
            'due_date' => now()->subDay(),
            'status' => 'in_progress'
        ]);
        $this->assertTrue($this->workOrder->isOverdue());

        $this->workOrder->update([
            'due_date' => now()->addDay()
        ]);
        $this->assertFalse($this->workOrder->isOverdue());

        $this->workOrder->update([
            'status' => 'completed',
            'due_date' => now()->subDay()
        ]);
        $this->assertFalse($this->workOrder->isOverdue());
    }

    /** @test */
    public function it_can_determine_if_completed()
    {
        $this->workOrder->update(['status' => 'completed']);
        $this->assertTrue($this->workOrder->isCompleted());

        $this->workOrder->update(['status' => 'in_progress']);
        $this->assertFalse($this->workOrder->isCompleted());
    }

    /** @test */
    public function it_can_determine_if_cancelled()
    {
        $this->workOrder->update(['status' => 'cancelled']);
        $this->assertTrue($this->workOrder->isCancelled());

        $this->workOrder->update(['status' => 'in_progress']);
        $this->assertFalse($this->workOrder->isCancelled());
    }

    /** @test */
    public function it_can_calculate_duration()
    {
        $this->workOrder->update([
            'started_at' => now()->subHours(5),
            'completed_at' => now()
        ]);

        $this->assertEquals(5, $this->workOrder->duration());
    }

    /** @test */
    public function it_can_track_progress()
    {
        $this->workOrder->update([
            'progress' => 75,
            'status' => 'in_progress'
        ]);

        $this->assertEquals(75, $this->workOrder->progress);
        $this->assertTrue($this->workOrder->isInProgress());
    }

    /** @test */
    public function it_can_get_activity_history()
    {
        // Create test activities
        $this->workOrder->activities()->createMany([
            [
                'type' => 'status_change',
                'description' => 'Status changed from open to in_progress',
                'user_id' => $this->assignedUser->id
            ],
            [
                'type' => 'comment',
                'description' => 'Added a comment',
                'user_id' => $this->assignedUser->id
            ]
        ]);

        $activities = $this->workOrder->activities;
        
        $this->assertCount(2, $activities);
        $this->assertEquals('status_change', $activities->first()->type);
        $this->assertEquals('comment', $activities->last()->type);
    }

    /** @test */
    public function it_can_calculate_cost()
    {
        $this->workOrder->update([
            'labor_cost' => 500,
            'material_cost' => 300,
            'additional_cost' => 200
        ]);

        $this->assertEquals(1000, $this->workOrder->totalCost());
    }

    /** @test */
    public function it_can_get_related_work_orders()
    {
        // Create related work orders for the same asset
        WorkOrder::factory()->count(2)->create([
            'asset_id' => $this->asset->id
        ]);

        $relatedWorkOrders = $this->workOrder->relatedWorkOrders();
        
        $this->assertCount(2, $relatedWorkOrders);
        $this->assertTrue($relatedWorkOrders->every(fn ($wo) => $wo->asset_id === $this->asset->id));
    }
} 