<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Space;
use App\Models\Asset;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class FloorControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Building $building;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and authenticate
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);

        // Create test building
        $this->building = Building::factory()->create();
    }

    /** @test */
    public function user_can_get_list_of_floors()
    {
        // Create test floors
        Floor::factory()->count(3)->create([
            'building_id' => $this->building->id
        ]);

        $response = $this->getJson('/api/floors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'building_id',
                        'name',
                        'number',
                        'total_area',
                        'status',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_create_a_floor()
    {
        $floorData = [
            'building_id' => $this->building->id,
            'name' => 'First Floor',
            'number' => 1,
            'total_area' => 10000,
            'status' => 'active',
            'metadata' => [
                'floor_type' => 'Office',
                'ceiling_height' => '10ft',
                'max_occupancy' => 100,
                'emergency_exits' => 2
            ]
        ];

        $response = $this->postJson('/api/floors', $floorData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'building_id',
                    'name',
                    'number',
                    'total_area',
                    'status',
                    'metadata',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('floors', [
            'name' => 'First Floor',
            'number' => 1
        ]);
    }

    /** @test */
    public function user_can_view_a_floor()
    {
        $floor = Floor::factory()->create([
            'building_id' => $this->building->id
        ]);

        $response = $this->getJson("/api/floors/{$floor->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'building_id',
                    'name',
                    'number',
                    'total_area',
                    'status',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /** @test */
    public function user_can_update_a_floor()
    {
        $floor = Floor::factory()->create([
            'building_id' => $this->building->id
        ]);

        $updateData = [
            'name' => 'Updated Floor Name',
            'status' => 'maintenance',
            'total_area' => 12000
        ];

        $response = $this->putJson("/api/floors/{$floor->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('floors', $updateData);
    }

    /** @test */
    public function user_can_delete_a_floor()
    {
        $floor = Floor::factory()->create([
            'building_id' => $this->building->id
        ]);

        $response = $this->deleteJson("/api/floors/{$floor->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('floors', ['id' => $floor->id]);
    }

    /** @test */
    public function user_cannot_create_floor_with_invalid_data()
    {
        $invalidData = [
            'building_id' => 999, // Non-existent building
            'name' => '',  // Required
            'number' => 'not-a-number', // Must be numeric
            'status' => 'invalid-status' // Invalid status
        ];

        $response = $this->postJson('/api/floors', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['building_id', 'name', 'number', 'status']);
    }

    /** @test */
    public function user_can_get_floor_spaces()
    {
        $floor = Floor::factory()->create([
            'building_id' => $this->building->id
        ]);

        Space::factory()->count(3)->create([
            'floor_id' => $floor->id
        ]);

        $response = $this->getJson("/api/floors/{$floor->id}/spaces");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'floor_id',
                        'name',
                        'type',
                        'area',
                        'status',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_get_floor_assets()
    {
        $floor = Floor::factory()->create([
            'building_id' => $this->building->id
        ]);

        Asset::factory()->count(3)->create([
            'floor_id' => $floor->id
        ]);

        $response = $this->getJson("/api/floors/{$floor->id}/assets");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'code',
                        'name',
                        'category_id',
                        'status',
                        'building_id',
                        'floor_id',
                        'space_id',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_get_floor_work_orders()
    {
        $floor = Floor::factory()->create([
            'building_id' => $this->building->id
        ]);

        WorkOrder::factory()->count(3)->create([
            'floor_id' => $floor->id
        ]);

        $response = $this->getJson("/api/floors/{$floor->id}/work-orders");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'code',
                        'title',
                        'description',
                        'status',
                        'priority',
                        'due_date',
                        'assigned_to',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_get_floor_statistics()
    {
        $floor = Floor::factory()->create([
            'building_id' => $this->building->id
        ]);

        // Create test data for statistics
        Space::factory()->count(2)->create([
            'floor_id' => $floor->id,
            'area' => 500,
            'status' => 'occupied'
        ]);

        Space::factory()->create([
            'floor_id' => $floor->id,
            'area' => 500,
            'status' => 'vacant'
        ]);

        Asset::factory()->count(3)->create([
            'floor_id' => $floor->id
        ]);

        WorkOrder::factory()->count(2)->create([
            'floor_id' => $floor->id,
            'status' => 'in_progress'
        ]);

        $response = $this->getJson("/api/floors/{$floor->id}/statistics");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_area',
                    'occupied_area',
                    'vacant_area',
                    'occupancy_rate',
                    'total_spaces',
                    'total_assets',
                    'active_work_orders',
                    'maintenance_costs'
                ]
            ]);
    }
} 