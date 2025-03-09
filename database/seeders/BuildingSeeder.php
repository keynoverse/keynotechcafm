<?php

namespace Database\Seeders;

use App\Models\Building;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    public function run(): void
    {
        // Create main office building
        Building::factory()->create([
            'name' => 'Main Office Building',
            'code' => 'HQ001',
            'description' => 'Corporate headquarters building with office spaces and meeting rooms',
            'total_floors' => 10,
            'total_area' => 25000,
            'status' => 'active',
            'metadata' => [
                'construction_type' => 'Steel and Glass',
                'occupancy_type' => 'Commercial',
                'facilities_manager' => [
                    'name' => 'John Smith',
                    'phone' => '555-0100',
                    'email' => 'fm@example.com'
                ],
                'certifications' => ['LEED Gold', 'ENERGY STAR']
            ]
        ]);

        // Create research and development building
        Building::factory()->create([
            'name' => 'R&D Center',
            'code' => 'RD001',
            'description' => 'Research and development facility with laboratories and testing areas',
            'total_floors' => 5,
            'total_area' => 15000,
            'status' => 'active',
            'metadata' => [
                'construction_type' => 'Concrete',
                'occupancy_type' => 'Research',
                'special_requirements' => [
                    'Clean rooms',
                    'Environmental controls',
                    'Specialized ventilation'
                ]
            ]
        ]);

        // Create warehouse
        Building::factory()->create([
            'name' => 'Central Warehouse',
            'code' => 'WH001',
            'description' => 'Storage and distribution facility',
            'total_floors' => 2,
            'total_area' => 20000,
            'status' => 'active',
            'metadata' => [
                'construction_type' => 'Steel',
                'occupancy_type' => 'Industrial',
                'loading_docks' => 8,
                'storage_capacity' => '15000 pallets'
            ]
        ]);

        // Create building under renovation
        Building::factory()
            ->underRenovation()
            ->create([
                'name' => 'Innovation Center',
                'code' => 'IC001',
                'description' => 'Innovation and technology center (under renovation)',
                'total_floors' => 4,
                'total_area' => 12000
            ]);

        // Create some random buildings
        Building::factory()
            ->count(3)
            ->create();

        // Create a building under maintenance
        Building::factory()
            ->underMaintenance()
            ->create();

        // Create an inactive building
        Building::factory()
            ->inactive()
            ->create();
    }
} 