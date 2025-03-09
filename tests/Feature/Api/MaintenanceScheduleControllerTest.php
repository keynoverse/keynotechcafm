<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Space;
use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceLog;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class MaintenanceScheduleControllerTest extends TestCase
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
    public function user_can_get_list_of_maintenance_schedules()
    {
        // Create test maintenance schedules
        MaintenanceSchedule::factory()->count(3)->create([
            'asset_id' => $this->asset->id
        ]);

        $response = $this->getJson('/api/maintenance-schedules');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'asset_id',
                        'title',
                        'description',
                        'frequency',
                        'frequency_unit',
                        'next_due_date',
                        'last_completed_at',
                        'assigned_to',
                        'status',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_create_a_maintenance_schedule()
    {
        $scheduleData = [
            'asset_id' => $this->asset->id,
            'title' => 'Monthly Maintenance',
            'description' => 'Regular monthly maintenance check',
            'frequency' => 30,
            'frequency_unit' => 'days',
            'next_due_date' => now()->addDays(30)->toDateString(),
            'assigned_to' => $this->user->id,
            'status' => 'active',
            'metadata' => [
                'checklist' => [
                    'inspect_components',
                    'clean_filters',
                    'lubricate_parts'
                ],
                'estimated_duration' => '2 hours',
                'required_tools' => ['wrench', 'screwdriver'],
                'safety_requirements' => ['safety_glasses', 'gloves']
            ]
        ];

        $response = $this->postJson('/api/maintenance-schedules', $scheduleData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'asset_id',
                    'title',
                    'description',
                    'frequency',
                    'frequency_unit',
                    'next_due_date',
                    'assigned_to',
                    'status',
                    'metadata',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('maintenance_schedules', [
            'title' => 'Monthly Maintenance',
            'frequency' => 30
        ]);
    }

    /** @test */
    public function user_can_view_a_maintenance_schedule()
    {
        $schedule = MaintenanceSchedule::factory()->create([
            'asset_id' => $this->asset->id
        ]);

        $response = $this->getJson("/api/maintenance-schedules/{$schedule->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'asset_id',
                    'title',
                    'description',
                    'frequency',
                    'frequency_unit',
                    'next_due_date',
                    'last_completed_at',
                    'assigned_to',
                    'status',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /** @test */
    public function user_can_update_a_maintenance_schedule()
    {
        $schedule = MaintenanceSchedule::factory()->create([
            'asset_id' => $this->asset->id
        ]);

        $updateData = [
            'title' => 'Updated Maintenance Schedule',
            'frequency' => 45,
            'status' => 'paused'
        ];

        $response = $this->putJson("/api/maintenance-schedules/{$schedule->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('maintenance_schedules', $updateData);
    }

    /** @test */
    public function user_can_delete_a_maintenance_schedule()
    {
        $schedule = MaintenanceSchedule::factory()->create([
            'asset_id' => $this->asset->id
        ]);

        $response = $this->deleteJson("/api/maintenance-schedules/{$schedule->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('maintenance_schedules', ['id' => $schedule->id]);
    }

    /** @test */
    public function user_cannot_create_maintenance_schedule_with_invalid_data()
    {
        $invalidData = [
            'asset_id' => 999, // Non-existent asset
            'title' => '',  // Required
            'frequency' => 'not-a-number', // Must be numeric
            'status' => 'invalid-status' // Invalid status
        ];

        $response = $this->postJson('/api/maintenance-schedules', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['asset_id', 'title', 'frequency', 'status']);
    }

    /** @test */
    public function user_can_get_maintenance_logs()
    {
        $schedule = MaintenanceSchedule::factory()->create([
            'asset_id' => $this->asset->id
        ]);

        MaintenanceLog::factory()->count(3)->create([
            'maintenance_schedule_id' => $schedule->id
        ]);

        $response = $this->getJson("/api/maintenance-schedules/{$schedule->id}/logs");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'maintenance_schedule_id',
                        'performed_by',
                        'status',
                        'notes',
                        'started_at',
                        'completed_at',
                        'cost',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_get_work_orders()
    {
        $schedule = MaintenanceSchedule::factory()->create([
            'asset_id' => $this->asset->id
        ]);

        WorkOrder::factory()->count(3)->create([
            'maintenance_schedule_id' => $schedule->id
        ]);

        $response = $this->getJson("/api/maintenance-schedules/{$schedule->id}/work-orders");

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
    public function user_can_generate_work_order()
    {
        $schedule = MaintenanceSchedule::factory()->create([
            'asset_id' => $this->asset->id
        ]);

        $response = $this->postJson("/api/maintenance-schedules/{$schedule->id}/generate-work-order");

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
                    'maintenance_schedule_id',
                    'due_date',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('work_orders', [
            'maintenance_schedule_id' => $schedule->id,
            'type' => 'preventive'
        ]);
    }

    /** @test */
    public function user_can_get_maintenance_statistics()
    {
        $schedule = MaintenanceSchedule::factory()->create([
            'asset_id' => $this->asset->id
        ]);

        // Create test maintenance logs
        MaintenanceLog::factory()->count(3)->create([
            'maintenance_schedule_id' => $schedule->id,
            'status' => 'completed',
            'cost' => 500
        ]);

        $response = $this->getJson("/api/maintenance-schedules/{$schedule->id}/statistics");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'completion_rate',
                    'average_completion_time',
                    'total_cost',
                    'completed_count',
                    'missed_count',
                    'next_due_date',
                    'last_completed_at'
                ]
            ]);
    }
} 