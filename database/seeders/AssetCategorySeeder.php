<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Create main categories
        $furniture = AssetCategory::factory()
            ->furniture()
            ->create([
                'name' => 'Furniture',
                'description' => 'Office furniture and fixtures'
            ]);

        $it = AssetCategory::factory()
            ->itEquipment()
            ->create([
                'name' => 'IT Equipment',
                'description' => 'Computer hardware and networking equipment'
            ]);

        $hvac = AssetCategory::factory()
            ->hvac()
            ->create([
                'name' => 'HVAC',
                'description' => 'Heating, ventilation, and air conditioning equipment'
            ]);

        // Create furniture subcategories
        $furnitureSubcategories = [
            'Desks' => 'Office desks and workstations',
            'Chairs' => 'Office chairs and seating',
            'Storage' => 'Cabinets, shelves, and storage units',
            'Tables' => 'Conference and meeting tables',
            'Partitions' => 'Office partitions and dividers'
        ];

        foreach ($furnitureSubcategories as $name => $description) {
            AssetCategory::factory()
                ->asChild($furniture)
                ->create([
                    'name' => $name,
                    'description' => $description
                ]);
        }

        // Create IT equipment subcategories
        $itSubcategories = [
            'Computers' => 'Desktop and laptop computers',
            'Networking' => 'Network equipment and infrastructure',
            'Peripherals' => 'Printers, scanners, and other peripherals',
            'Servers' => 'Server hardware and equipment',
            'Communication' => 'Phones and communication equipment'
        ];

        foreach ($itSubcategories as $name => $description) {
            AssetCategory::factory()
                ->asChild($it)
                ->create([
                    'name' => $name,
                    'description' => $description
                ]);
        }

        // Create HVAC subcategories
        $hvacSubcategories = [
            'Air Handlers' => 'Air handling units and equipment',
            'Chillers' => 'Chiller units and cooling systems',
            'Boilers' => 'Heating boilers and equipment',
            'Controls' => 'HVAC control systems',
            'Ventilation' => 'Ventilation and exhaust systems'
        ];

        foreach ($hvacSubcategories as $name => $description) {
            AssetCategory::factory()
                ->asChild($hvac)
                ->create([
                    'name' => $name,
                    'description' => $description
                ]);
        }

        // Create other main categories
        $categories = [
            'Electrical' => 'Electrical systems and equipment',
            'Plumbing' => 'Plumbing systems and fixtures',
            'Safety' => 'Safety and security equipment',
            'Transportation' => 'Transportation and logistics equipment',
            'Tools' => 'Maintenance tools and equipment'
        ];

        foreach ($categories as $name => $description) {
            $category = AssetCategory::factory()->create([
                'name' => $name,
                'description' => $description
            ]);

            // Create 3-5 subcategories for each main category
            $count = rand(3, 5);
            AssetCategory::factory()
                ->count($count)
                ->asChild($category)
                ->create();
        }

        // Set some categories as inactive
        AssetCategory::inRandomOrder()
            ->limit(3)
            ->get()
            ->each(function ($category) {
                $category->update(['status' => 'inactive']);
            });
    }
} 