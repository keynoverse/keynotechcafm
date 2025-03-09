<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Building management
            'view buildings',
            'create buildings',
            'edit buildings',
            'delete buildings',
            
            // Floor management
            'view floors',
            'create floors',
            'edit floors',
            'delete floors',
            
            // Space management
            'view spaces',
            'create spaces',
            'edit spaces',
            'delete spaces',
            
            // Asset management
            'view assets',
            'create assets',
            'edit assets',
            'delete assets',
            'manage asset categories',
            
            // Maintenance management
            'view maintenance schedules',
            'create maintenance schedules',
            'edit maintenance schedules',
            'delete maintenance schedules',
            'view maintenance logs',
            'create maintenance logs',
            'edit maintenance logs',
            'delete maintenance logs',
            
            // Work order management
            'view work orders',
            'create work orders',
            'edit work orders',
            'delete work orders',
            'assign work orders',
            'close work orders',
            'add work order comments',
            'edit work order comments',
            'delete work order comments',
            'upload work order attachments',
            'delete work order attachments',
            
            // Reports and analytics
            'view reports',
            'export reports',
            'view analytics',
            
            // System settings
            'manage settings',
            'manage roles',
            'view audit logs'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $roles = [
            'Super Admin' => $permissions,
            
            'Facility Manager' => [
                'view users',
                'view buildings', 'create buildings', 'edit buildings',
                'view floors', 'create floors', 'edit floors',
                'view spaces', 'create spaces', 'edit spaces',
                'view assets', 'create assets', 'edit assets', 'manage asset categories',
                'view maintenance schedules', 'create maintenance schedules', 'edit maintenance schedules',
                'view maintenance logs', 'create maintenance logs',
                'view work orders', 'create work orders', 'edit work orders', 'assign work orders', 'close work orders',
                'add work order comments', 'edit work order comments',
                'upload work order attachments',
                'view reports', 'export reports', 'view analytics',
                'view audit logs'
            ],
            
            'Maintenance Supervisor' => [
                'view buildings', 'view floors', 'view spaces',
                'view assets', 'edit assets',
                'view maintenance schedules', 'create maintenance schedules', 'edit maintenance schedules',
                'view maintenance logs', 'create maintenance logs', 'edit maintenance logs',
                'view work orders', 'create work orders', 'edit work orders', 'assign work orders', 'close work orders',
                'add work order comments', 'edit work order comments',
                'upload work order attachments',
                'view reports', 'view analytics'
            ],
            
            'Maintenance Technician' => [
                'view buildings', 'view floors', 'view spaces',
                'view assets',
                'view maintenance schedules',
                'view maintenance logs', 'create maintenance logs',
                'view work orders', 'edit work orders', 'close work orders',
                'add work order comments',
                'upload work order attachments'
            ],
            
            'Employee' => [
                'view buildings', 'view floors', 'view spaces',
                'view assets',
                'view work orders', 'create work orders',
                'add work order comments',
                'upload work order attachments'
            ]
        ];

        foreach ($roles as $role => $rolePermissions) {
            $createdRole = Role::create(['name' => $role]);
            $createdRole->givePermissionTo($rolePermissions);
        }
    }
} 