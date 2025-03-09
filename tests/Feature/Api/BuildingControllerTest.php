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

class BuildingControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and authenticate
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function user_can_get_list_of_buildings()
    {
        // Create test buildings
        Building::factory()->count(3)->create();

        $response = $this->getJson('/api/buildings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'code',
                        'name',
                        'address',
                        'city',
                        'state',
                        'country',
                        'postal_code',
                        'total_floors',
                        'total_area',
                        'year_built',
                        'status',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_create_a_building()
    {
        $buildingData = [
            'code' => 'BLD001',
            'name' => 'Test Building',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'state' => 'Test State',
            'country' => 'Test Country',
            'postal_code' => '12345',
            'total_floors' => 10,
            'total_area' => 50000,
            'year_built' => 2020,
            'status' => 'active',
            'metadata' => [
                'construction_type' => 'Commercial',
                'occupancy_type' => 'Office',
                'facilities_manager' => 'John Doe',
                'emergency_contact' => '+1234567890'
            ]
        ];

        $response = $this->postJson('/api/buildings', $buildingData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'code',
                    'name',
                    'address',
                    'city',
                    'state',
                    'country',
                    'postal_code',
                    'total_floors',
                    'total_area',
                    'year_built',
                    'status',
                    'metadata',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('buildings', [
            'code' => 'BLD001',
            'name' => 'Test Building'
        ]);
    }

    /** @test */
    public function user_can_view_a_building()
    {
        $building = Building::factory()->create();

        $response = $this->getJson("/api/buildings/{$building->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'code',
                    'name',
                    'address',
                    'city',
                    'state',
                    'country',
                    'postal_code',
                    'total_floors',
                    'total_area',
                    'year_built',
                    'status',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /** @test */
    public function user_can_update_a_building()
    {
        $building = Building::factory()->create();

        $updateData = [
            'name' => 'Updated Building Name',
            'status' => 'maintenance',
            'address' => 'Updated Address'
        ];

        $response = $this->putJson("/api/buildings/{$building->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('buildings', $updateData);
    }

    /** @test */
    public function user_can_delete_a_building()
    {
        $building = Building::factory()->create();

        $response = $this->deleteJson("/api/buildings/{$building->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('buildings', ['id' => $building->id]);
    }

    /** @test */
    public function user_cannot_create_building_with_invalid_data()
    {
        $invalidData = [
            'code' => '',  // Required
            'name' => '',  // Required
            'total_floors' => 'not-a-number', // Must be numeric
            'status' => 'invalid-status' // Invalid status
        ];

        $response = $this->postJson('/api/buildings', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'total_floors', 'status']);
    }

    /** @test */
    public function user_can_get_building_floors()
    {
        $building = Building::factory()->create();
        Floor::factory()->count(3)->create(['building_id' => $building->id]);

        $response = $this->getJson("/api/buildings/{$building->id}/floors");

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
    public function user_can_get_building_spaces()
    {
        $building = Building::factory()->create();
        $floor = Floor::factory()->create(['building_id' => $building->id]);
        Space::factory()->count(3)->create(['floor_id' => $floor->id]);

        $response = $this->getJson("/api/buildings/{$building->id}/spaces");

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
    public function user_can_get_building_assets()
    {
        $building = Building::factory()->create();
        Asset::factory()->count(3)->create(['building_id' => $building->id]);

        $response = $this->getJson("/api/buildings/{$building->id}/assets");

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
    public function user_can_get_building_work_orders()
    {
        $building = Building::factory()->create();
        WorkOrder::factory()->count(3)->create(['building_id' => $building->id]);

        $response = $this->getJson("/api/buildings/{$building->id}/work-orders");

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
    public function user_can_get_building_statistics()
    {
        $building = Building::factory()->create();
        
        // Create test data for statistics
        Floor::factory()->count(2)->create([
            'building_id' => $building->id,
            'total_area' => 1000
        ]);

        Asset::factory()->count(3)->create([
            'building_id' => $building->id
        ]);

        WorkOrder::factory()->count(2)->create([
            'building_id' => $building->id,
            'status' => 'in_progress'
        ]);

        $response = $this->getJson("/api/buildings/{$building->id}/statistics");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_area',
                    'total_floors',
                    'total_spaces',
                    'total_assets',
                    'active_work_orders',
                    'occupancy_rate',
                    'maintenance_costs'
                ]
            ]);
    }
} 