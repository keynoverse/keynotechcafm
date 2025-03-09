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

class SpaceControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Building $building;
    private Floor $floor;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and authenticate
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);

        // Create test building and floor
        $this->building = Building::factory()->create();
        $this->floor = Floor::factory()->create([
            'building_id' => $this->building->id
        ]);
    }

    /** @test */
    public function user_can_get_list_of_spaces()
    {
        // Create test spaces
        Space::factory()->count(3)->create([
            'floor_id' => $this->floor->id
        ]);

        $response = $this->getJson('/api/spaces');

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
                        'occupant_id',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_create_a_space()
    {
        $spaceData = [
            'floor_id' => $this->floor->id,
            'name' => 'Conference Room A',
            'type' => 'meeting_room',
            'area' => 500,
            'status' => 'vacant',
            'metadata' => [
                'capacity' => 20,
                'amenities' => ['projector', 'whiteboard', 'video_conferencing'],
                'access_requirements' => 'keycard',
                'operating_hours' => '8:00-18:00'
            ]
        ];

        $response = $this->postJson('/api/spaces', $spaceData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'floor_id',
                    'name',
                    'type',
                    'area',
                    'status',
                    'metadata',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('spaces', [
            'name' => 'Conference Room A',
            'type' => 'meeting_room'
        ]);
    }

    /** @test */
    public function user_can_view_a_space()
    {
        $space = Space::factory()->create([
            'floor_id' => $this->floor->id
        ]);

        $response = $this->getJson("/api/spaces/{$space->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'floor_id',
                    'name',
                    'type',
                    'area',
                    'status',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /** @test */
    public function user_can_update_a_space()
    {
        $space = Space::factory()->create([
            'floor_id' => $this->floor->id
        ]);

        $updateData = [
            'name' => 'Updated Space Name',
            'status' => 'occupied',
            'area' => 600
        ];

        $response = $this->putJson("/api/spaces/{$space->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('spaces', $updateData);
    }

    /** @test */
    public function user_can_delete_a_space()
    {
        $space = Space::factory()->create([
            'floor_id' => $this->floor->id
        ]);

        $response = $this->deleteJson("/api/spaces/{$space->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('spaces', ['id' => $space->id]);
    }

    /** @test */
    public function user_cannot_create_space_with_invalid_data()
    {
        $invalidData = [
            'floor_id' => 999, // Non-existent floor
            'name' => '',  // Required
            'area' => 'not-a-number', // Must be numeric
            'status' => 'invalid-status' // Invalid status
        ];

        $response = $this->postJson('/api/spaces', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['floor_id', 'name', 'area', 'status']);
    }

    /** @test */
    public function user_can_get_space_assets()
    {
        $space = Space::factory()->create([
            'floor_id' => $this->floor->id
        ]);

        Asset::factory()->count(3)->create([
            'space_id' => $space->id
        ]);

        $response = $this->getJson("/api/spaces/{$space->id}/assets");

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
    public function user_can_get_space_work_orders()
    {
        $space = Space::factory()->create([
            'floor_id' => $this->floor->id
        ]);

        WorkOrder::factory()->count(3)->create([
            'space_id' => $space->id
        ]);

        $response = $this->getJson("/api/spaces/{$space->id}/work-orders");

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
    public function user_can_get_space_occupancy_history()
    {
        $space = Space::factory()->create([
            'floor_id' => $this->floor->id
        ]);

        $space->occupancyHistory()->createMany([
            [
                'status' => 'occupied',
                'start_date' => now()->subDays(30),
                'end_date' => now()->subDays(15)
            ],
            [
                'status' => 'vacant',
                'start_date' => now()->subDays(15),
                'end_date' => now()
            ]
        ]);

        $response = $this->getJson("/api/spaces/{$space->id}/occupancy-history");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'space_id',
                        'status',
                        'start_date',
                        'end_date',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function user_can_get_space_statistics()
    {
        $space = Space::factory()->create([
            'floor_id' => $this->floor->id
        ]);

        // Create test data for statistics
        Asset::factory()->count(3)->create([
            'space_id' => $space->id
        ]);

        WorkOrder::factory()->count(2)->create([
            'space_id' => $space->id,
            'status' => 'in_progress'
        ]);

        $space->occupancyHistory()->createMany([
            [
                'status' => 'occupied',
                'start_date' => now()->subDays(30),
                'end_date' => now()->subDays(15)
            ],
            [
                'status' => 'vacant',
                'start_date' => now()->subDays(15),
                'end_date' => now()
            ]
        ]);

        $response = $this->getJson("/api/spaces/{$space->id}/statistics");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_assets',
                    'active_work_orders',
                    'occupancy_rate',
                    'maintenance_costs',
                    'last_maintenance_date',
                    'next_maintenance_due'
                ]
            ]);
    }
} 