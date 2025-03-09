<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Space;
use App\Models\Asset;
use App\Models\WorkOrder;
use App\Models\Comment;
use App\Models\Attachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WorkOrderControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Building $building;
    private Floor $floor;
    private Space $space;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and authenticate
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);

        // Create test data
        $this->building = Building::factory()->create();
        $this->floor = Floor::factory()->create(['building_id' => $this->building->id]);
        $this->space = Space::factory()->create(['floor_id' => $this->floor->id]);
        $this->asset = Asset::factory()->create([
            'building_id' => $this->building->id,
            'floor_id' => $this->floor->id,
            'space_id' => $this->space->id
        ]);
    }

    /** @test */
    public function user_can_get_list_of_work_orders()
    {
        // Create test work orders
        WorkOrder::factory()->count(3)->create([
            'asset_id' => $this->asset->id,
            'building_id' => $this->building->id,
            'floor_id' => $this->floor->id,
            'space_id' => $this->space->id
        ]);

        $response = $this->getJson('/api/work-orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'code',
                        'title',
                        'description',
                        'type',
                        'priority',
                        'status',
                        'assigned_to',
                        'asset_id',
                        'building_id',
                        'floor_id',
                        'space_id',
                        'due_date',
                        'started_at',
                        'completed_at',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_create_a_work_order()
    {
        $workOrderData = [
            'code' => 'WO001',
            'title' => 'Test Work Order',
            'description' => 'Test work order description',
            'type' => 'corrective',
            'priority' => 'high',
            'status' => 'open',
            'assigned_to' => $this->user->id,
            'asset_id' => $this->asset->id,
            'building_id' => $this->building->id,
            'floor_id' => $this->floor->id,
            'space_id' => $this->space->id,
            'due_date' => now()->addDays(7)->toDateString(),
            'metadata' => [
                'estimated_duration' => '2 hours',
                'required_tools' => ['wrench', 'screwdriver'],
                'safety_requirements' => ['safety_glasses', 'gloves'],
                'estimated_cost' => 500
            ]
        ];

        $response = $this->postJson('/api/work-orders', $workOrderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'code',
                    'title',
                    'description',
                    'type',
                    'priority',
                    'status',
                    'assigned_to',
                    'asset_id',
                    'building_id',
                    'floor_id',
                    'space_id',
                    'due_date',
                    'metadata',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('work_orders', [
            'code' => 'WO001',
            'title' => 'Test Work Order'
        ]);
    }

    /** @test */
    public function user_can_view_a_work_order()
    {
        $workOrder = WorkOrder::factory()->create([
            'asset_id' => $this->asset->id,
            'building_id' => $this->building->id,
            'floor_id' => $this->floor->id,
            'space_id' => $this->space->id
        ]);

        $response = $this->getJson("/api/work-orders/{$workOrder->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'code',
                    'title',
                    'description',
                    'type',
                    'priority',
                    'status',
                    'assigned_to',
                    'asset_id',
                    'building_id',
                    'floor_id',
                    'space_id',
                    'due_date',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /** @test */
    public function user_can_update_a_work_order()
    {
        $workOrder = WorkOrder::factory()->create([
            'asset_id' => $this->asset->id,
            'building_id' => $this->building->id,
            'floor_id' => $this->floor->id,
            'space_id' => $this->space->id
        ]);

        $updateData = [
            'title' => 'Updated Work Order',
            'status' => 'in_progress',
            'priority' => 'medium',
            'progress' => 50
        ];

        $response = $this->putJson("/api/work-orders/{$workOrder->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('work_orders', $updateData);
    }

    /** @test */
    public function user_can_delete_a_work_order()
    {
        $workOrder = WorkOrder::factory()->create([
            'asset_id' => $this->asset->id,
            'building_id' => $this->building->id,
            'floor_id' => $this->floor->id,
            'space_id' => $this->space->id
        ]);

        $response = $this->deleteJson("/api/work-orders/{$workOrder->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('work_orders', ['id' => $workOrder->id]);
    }

    /** @test */
    public function user_cannot_create_work_order_with_invalid_data()
    {
        $invalidData = [
            'code' => '',  // Required
            'title' => '',  // Required
            'asset_id' => 999, // Non-existent asset
            'priority' => 'invalid-priority', // Invalid priority
            'status' => 'invalid-status' // Invalid status
        ];

        $response = $this->postJson('/api/work-orders', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'title', 'asset_id', 'priority', 'status']);
    }

    /** @test */
    public function user_can_add_comment_to_work_order()
    {
        $workOrder = WorkOrder::factory()->create([
            'asset_id' => $this->asset->id
        ]);

        $commentData = [
            'content' => 'Test comment',
            'user_id' => $this->user->id
        ];

        $response = $this->postJson("/api/work-orders/{$workOrder->id}/comments", $commentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'work_order_id',
                    'user_id',
                    'content',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'work_order_id' => $workOrder->id,
            'content' => 'Test comment'
        ]);
    }

    /** @test */
    public function user_can_upload_attachment_to_work_order()
    {
        Storage::fake('local');

        $workOrder = WorkOrder::factory()->create([
            'asset_id' => $this->asset->id
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->postJson("/api/work-orders/{$workOrder->id}/attachments", [
            'file' => $file,
            'description' => 'Test attachment'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'work_order_id',
                    'file_path',
                    'file_name',
                    'file_size',
                    'description',
                    'created_at',
                    'updated_at'
                ]
            ]);

        Storage::disk('local')->assertExists('attachments/' . $file->hashName());
    }

    /** @test */
    public function user_can_get_work_order_history()
    {
        $workOrder = WorkOrder::factory()->create([
            'asset_id' => $this->asset->id
        ]);

        // Create test activities
        $workOrder->activities()->createMany([
            [
                'type' => 'status_change',
                'description' => 'Status changed from open to in_progress',
                'user_id' => $this->user->id
            ],
            [
                'type' => 'comment',
                'description' => 'Added a comment',
                'user_id' => $this->user->id
            ]
        ]);

        $response = $this->getJson("/api/work-orders/{$workOrder->id}/history");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'work_order_id',
                        'type',
                        'description',
                        'user_id',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function user_can_get_work_order_statistics()
    {
        $workOrder = WorkOrder::factory()->create([
            'asset_id' => $this->asset->id,
            'started_at' => now()->subDays(2),
            'labor_cost' => 500,
            'material_cost' => 300,
            'additional_cost' => 200
        ]);

        $response = $this->getJson("/api/work-orders/{$workOrder->id}/statistics");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'duration',
                    'total_cost',
                    'progress',
                    'comments_count',
                    'attachments_count',
                    'is_overdue'
                ]
            ]);
    }
} 