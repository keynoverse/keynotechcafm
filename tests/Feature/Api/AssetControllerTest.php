<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Space;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class AssetControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Category $category;
    private Building $building;
    private Floor $floor;
    private Space $space;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and authenticate
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);

        // Create test data
        $this->category = Category::factory()->create();
        $this->building = Building::factory()->create();
        $this->floor = Floor::factory()->create(['building_id' => $this->building->id]);
        $this->space = Space::factory()->create(['floor_id' => $this->floor->id]);
    }

    /** @test */
    public function user_can_get_list_of_assets()
    {
        // Create test assets
        Asset::factory()->count(3)->create();

        $response = $this->getJson('/api/assets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'code',
                        'name',
                        'category_id',
                        'status',
                        'description',
                        'building_id',
                        'floor_id',
                        'space_id',
                        'purchase_cost',
                        'purchase_date',
                        'warranty_expiry',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_create_an_asset()
    {
        $assetData = [
            'code' => 'AST001',
            'name' => 'Test Asset',
            'category_id' => $this->category->id,
            'status' => 'active',
            'description' => 'Test asset description',
            'building_id' => $this->building->id,
            'floor_id' => $this->floor->id,
            'space_id' => $this->space->id,
            'purchase_cost' => 1000.00,
            'purchase_date' => now()->toDateString(),
            'warranty_expiry' => now()->addYear()->toDateString(),
            'metadata' => [
                'manufacturer' => 'Test Manufacturer',
                'model' => 'Test Model',
                'serial_number' => 'SN123456',
                'maintenance_frequency' => 30
            ]
        ];

        $response = $this->postJson('/api/assets', $assetData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'code',
                    'name',
                    'category_id',
                    'status',
                    'description',
                    'building_id',
                    'floor_id',
                    'space_id',
                    'purchase_cost',
                    'purchase_date',
                    'warranty_expiry',
                    'metadata',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('assets', [
            'code' => 'AST001',
            'name' => 'Test Asset'
        ]);
    }

    /** @test */
    public function user_can_view_an_asset()
    {
        $asset = Asset::factory()->create();

        $response = $this->getJson("/api/assets/{$asset->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'code',
                    'name',
                    'category_id',
                    'status',
                    'description',
                    'building_id',
                    'floor_id',
                    'space_id',
                    'purchase_cost',
                    'purchase_date',
                    'warranty_expiry',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /** @test */
    public function user_can_update_an_asset()
    {
        $asset = Asset::factory()->create();

        $updateData = [
            'name' => 'Updated Asset Name',
            'status' => 'maintenance',
            'description' => 'Updated description'
        ];

        $response = $this->putJson("/api/assets/{$asset->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('assets', $updateData);
    }

    /** @test */
    public function user_can_delete_an_asset()
    {
        $asset = Asset::factory()->create();

        $response = $this->deleteJson("/api/assets/{$asset->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    /** @test */
    public function user_cannot_create_asset_with_invalid_data()
    {
        $invalidData = [
            'code' => '',  // Required
            'name' => '',  // Required
            'category_id' => 999, // Non-existent category
            'status' => 'invalid-status' // Invalid status
        ];

        $response = $this->postJson('/api/assets', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'category_id', 'status']);
    }

    /** @test */
    public function user_can_filter_assets_by_status()
    {
        // Create assets with different statuses
        Asset::factory()->count(2)->create(['status' => 'active']);
        Asset::factory()->create(['status' => 'maintenance']);
        Asset::factory()->create(['status' => 'repair']);

        $response = $this->getJson('/api/assets?status=active');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['status' => 'active']);
    }

    /** @test */
    public function user_can_filter_assets_by_category()
    {
        $category = Category::factory()->create();
        Asset::factory()->count(2)->create(['category_id' => $category->id]);
        Asset::factory()->create(); // Different category

        $response = $this->getJson("/api/assets?category_id={$category->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['category_id' => $category->id]);
    }

    /** @test */
    public function user_can_search_assets()
    {
        Asset::factory()->create(['name' => 'Test Asset ABC']);
        Asset::factory()->create(['name' => 'Another Asset XYZ']);
        Asset::factory()->create(['name' => 'Different Item']);

        $response = $this->getJson('/api/assets?search=Asset');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Test Asset ABC'])
            ->assertJsonFragment(['name' => 'Another Asset XYZ']);
    }

    /** @test */
    public function user_can_get_asset_maintenance_history()
    {
        $asset = Asset::factory()->create();
        $maintenanceLogs = MaintenanceLog::factory()->count(3)->create([
            'asset_id' => $asset->id
        ]);

        $response = $this->getJson("/api/assets/{$asset->id}/maintenance-history");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'asset_id',
                        'maintenance_date',
                        'type',
                        'description',
                        'cost',
                        'performed_by',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_get_asset_work_orders()
    {
        $asset = Asset::factory()->create();
        $workOrders = WorkOrder::factory()->count(3)->create([
            'asset_id' => $asset->id
        ]);

        $response = $this->getJson("/api/assets/{$asset->id}/work-orders");

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
} 