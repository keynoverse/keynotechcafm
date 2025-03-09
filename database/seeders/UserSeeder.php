<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'employee_id' => 'EMP001',
            'department' => 'Administration',
            'position' => 'System Administrator',
            'status' => 'active'
        ]);
        $superAdmin->assignRole('Super Admin');

        // Create Facility Manager
        $facilityManager = User::factory()->create([
            'name' => 'Facility Manager',
            'email' => 'fm@example.com',
            'password' => Hash::make('password'),
            'employee_id' => 'EMP002',
            'department' => 'Facilities',
            'position' => 'Facility Manager',
            'status' => 'active'
        ]);
        $facilityManager->assignRole('Facility Manager');

        // Create Maintenance Supervisor
        $supervisor = User::factory()->create([
            'name' => 'Maintenance Supervisor',
            'email' => 'supervisor@example.com',
            'password' => Hash::make('password'),
            'employee_id' => 'EMP003',
            'department' => 'Maintenance',
            'position' => 'Maintenance Supervisor',
            'status' => 'active'
        ]);
        $supervisor->assignRole('Maintenance Supervisor');

        // Create Maintenance Technicians
        User::factory()
            ->count(5)
            ->state(function (array $attributes) {
                return [
                    'department' => 'Maintenance',
                    'position' => 'Maintenance Technician'
                ];
            })
            ->create()
            ->each(function ($user) {
                $user->assignRole('Maintenance Technician');
            });

        // Create Regular Employees
        User::factory()
            ->count(10)
            ->create()
            ->each(function ($user) {
                $user->assignRole('Employee');
            });

        // Create some inactive users
        User::factory()
            ->count(3)
            ->state(function (array $attributes) {
                return [
                    'status' => 'inactive'
                ];
            })
            ->create()
            ->each(function ($user) {
                $user->assignRole('Employee');
            });
    }
} 