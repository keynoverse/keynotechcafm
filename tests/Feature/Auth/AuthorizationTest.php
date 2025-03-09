<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Asset;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $technician;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'manager']);
        $technicianRole = Role::create(['name' => 'technician']);
        $userRole = Role::create(['name' => 'user']);

        // Create permissions
        $permissions = [
            // Asset permissions
            'view_assets',
            'create_assets',
            'edit_assets',
            'delete_assets',
            // Work order permissions
            'view_work_orders',
            'create_work_orders',
            'edit_work_orders',
            'delete_work_orders',
            'assign_work_orders',
            // Maintenance schedule permissions
            'view_maintenance_schedules',
            'create_maintenance_schedules',
            'edit_maintenance_schedules',
            'delete_maintenance_schedules',
            // User management permissions
            'view_users',
            'create_users',
            'edit_users',
            'delete_users'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());
        
        $managerRole->givePermissionTo([
            'view_assets', 'create_assets', 'edit_assets',
            'view_work_orders', 'create_work_orders', 'edit_work_orders', 'assign_work_orders',
            'view_maintenance_schedules', 'create_maintenance_schedules', 'edit_maintenance_schedules',
            'view_users'
        ]);

        $technicianRole->givePermissionTo([
            'view_assets',
            'view_work_orders', 'edit_work_orders',
            'view_maintenance_schedules'
        ]);

        $userRole->givePermissionTo([
            'view_assets',
            'view_work_orders', 'create_work_orders'
        ]);

        // Create users with roles
        $this->admin = User::factory()->create(['email' => 'admin@example.com']);
        $this->admin->assignRole('admin');

        $this->manager = User::factory()->create(['email' => 'manager@example.com']);
        $this->manager->assignRole('manager');

        $this->technician = User::factory()->create(['email' => 'technician@example.com']);
        $this->technician->assignRole('technician');

        $this->user = User::factory()->create(['email' => 'user@example.com']);
        $this->user->assignRole('user');
    }

    /** @test */
    public function admin_can_access_all_resources()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/users');
        $response->assertStatus(200);

        $response = $this->getJson('/api/assets');
        $response->assertStatus(200);

        $response = $this->getJson('/api/work-orders');
        $response->assertStatus(200);

        $response = $this->getJson('/api/maintenance-schedules');
        $response->assertStatus(200);
    }

    /** @test */
    public function manager_has_limited_access()
    {
        Sanctum::actingAs($this->manager);

        // Can access
        $response = $this->getJson('/api/assets');
        $response->assertStatus(200);

        $response = $this->getJson('/api/work-orders');
        $response->assertStatus(200);

        // Cannot access
        $response = $this->deleteJson('/api/users/1');
        $response->assertStatus(403);

        $response = $this->deleteJson('/api/assets/1');
        $response->assertStatus(403);
    }

    /** @test */
    public function technician_has_restricted_access()
    {
        Sanctum::actingAs($this->technician);

        // Can access
        $response = $this->getJson('/api/assets');
        $response->assertStatus(200);

        $response = $this->getJson('/api/work-orders');
        $response->assertStatus(200);

        // Cannot access
        $response = $this->postJson('/api/assets', []);
        $response->assertStatus(403);

        $response = $this->deleteJson('/api/work-orders/1');
        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_has_minimal_access()
    {
        Sanctum::actingAs($this->user);

        // Can access
        $response = $this->getJson('/api/assets');
        $response->assertStatus(200);

        $response = $this->getJson('/api/work-orders');
        $response->assertStatus(200);

        // Cannot access
        $response = $this->postJson('/api/maintenance-schedules', []);
        $response->assertStatus(403);

        $response = $this->putJson('/api/assets/1', []);
        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_only_view_assigned_work_orders()
    {
        $user = User::factory()->create();
        $user->assignRole('technician');
        Sanctum::actingAs($user);

        // Create work orders
        $assignedWorkOrder = WorkOrder::factory()->create(['assigned_to' => $user->id]);
        $unassignedWorkOrder = WorkOrder::factory()->create();

        $response = $this->getJson('/api/work-orders');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $assignedWorkOrder->id])
            ->assertJsonMissing(['id' => $unassignedWorkOrder->id]);
    }

    /** @test */
    public function manager_can_assign_work_orders()
    {
        Sanctum::actingAs($this->manager);

        $workOrder = WorkOrder::factory()->create();
        $technician = User::factory()->create();

        $response = $this->putJson("/api/work-orders/{$workOrder->id}/assign", [
            'user_id' => $technician->id
        ]);

        $response->assertStatus(200);
        $this->assertEquals($technician->id, $workOrder->fresh()->assigned_to);
    }

    /** @test */
    public function technician_cannot_assign_work_orders()
    {
        Sanctum::actingAs($this->technician);

        $workOrder = WorkOrder::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->putJson("/api/work-orders/{$workOrder->id}/assign", [
            'user_id' => $otherUser->id
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_create_work_order_but_not_assign()
    {
        Sanctum::actingAs($this->user);

        $asset = Asset::factory()->create();
        
        // Can create work order
        $response = $this->postJson('/api/work-orders', [
            'title' => 'Test Work Order',
            'description' => 'Test Description',
            'asset_id' => $asset->id,
            'priority' => 'high'
        ]);

        $response->assertStatus(201);

        // Cannot assign work order
        $workOrder = WorkOrder::factory()->create();
        $response = $this->putJson("/api/work-orders/{$workOrder->id}/assign", [
            'user_id' => $this->technician->id
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_manage_roles_and_permissions()
    {
        Sanctum::actingAs($this->admin);

        // Create new role
        $response = $this->postJson('/api/roles', [
            'name' => 'supervisor',
            'permissions' => ['view_assets', 'view_work_orders']
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('roles', ['name' => 'supervisor']);

        // Assign role to user
        $user = User::factory()->create();
        $response = $this->putJson("/api/users/{$user->id}/roles", [
            'roles' => ['supervisor']
        ]);

        $response->assertStatus(200);
        $this->assertTrue($user->fresh()->hasRole('supervisor'));
    }

    /** @test */
    public function non_admin_cannot_manage_roles()
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/roles', [
            'name' => 'new_role',
            'permissions' => ['view_assets']
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_permissions_are_cached()
    {
        $user = User::factory()->create();
        $user->assignRole('technician');
        Sanctum::actingAs($user);

        // First request - permissions are cached
        $response = $this->getJson('/api/assets');
        $response->assertStatus(200);

        // Remove permission but cache remains
        $user->revokePermissionTo('view_assets');

        // Second request - should still work due to cache
        $response = $this->getJson('/api/assets');
        $response->assertStatus(200);

        // Clear permission cache
        $user->forgetCachedPermissions();

        // Third request - should fail now
        $response = $this->getJson('/api/assets');
        $response->assertStatus(403);
    }
} 