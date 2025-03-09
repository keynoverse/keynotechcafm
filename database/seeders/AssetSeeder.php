<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Space;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        // Get all spaces and categories
        $spaces = Space::all();
        $categories = AssetCategory::all();

        // Create assets for each space based on space type
        foreach ($spaces as $space) {
            switch ($space->type) {
                case 'office':
                    $this->createOfficeAssets($space);
                    break;

                case 'meeting':
                    $this->createMeetingRoomAssets($space);
                    break;

                case 'storage':
                    $this->createStorageAssets($space);
                    break;

                case 'technical':
                    $this->createTechnicalAssets($space);
                    break;

                case 'utility':
                    $this->createUtilityAssets($space);
                    break;

                case 'common':
                    $this->createCommonAreaAssets($space);
                    break;
            }
        }

        // Create some assets in maintenance
        Asset::inRandomOrder()
            ->limit(10)
            ->get()
            ->each(function ($asset) {
                $asset->update(['status' => 'maintenance']);
            });

        // Create some inactive assets
        Asset::inRandomOrder()
            ->limit(5)
            ->get()
            ->each(function ($asset) {
                $asset->update(['status' => 'inactive']);
            });

        // Create some assets in storage (not assigned to any space)
        Asset::factory()
            ->count(20)
            ->inStorage()
            ->create();
    }

    private function createOfficeAssets(Space $space): void
    {
        // Furniture
        $deskCategory = AssetCategory::where('name', 'Desks')->first();
        $chairCategory = AssetCategory::where('name', 'Chairs')->first();
        $storageCategory = AssetCategory::where('name', 'Storage')->first();

        // Create desks
        Asset::factory()
            ->count(rand(2, 6))
            ->forCategory($deskCategory)
            ->inSpace($space)
            ->create();

        // Create chairs
        Asset::factory()
            ->count(rand(2, 6))
            ->forCategory($chairCategory)
            ->inSpace($space)
            ->create();

        // Create storage units
        Asset::factory()
            ->count(rand(1, 3))
            ->forCategory($storageCategory)
            ->inSpace($space)
            ->create();

        // IT Equipment
        $computerCategory = AssetCategory::where('name', 'Computers')->first();
        Asset::factory()
            ->count(rand(2, 6))
            ->forCategory($computerCategory)
            ->inSpace($space)
            ->create();
    }

    private function createMeetingRoomAssets(Space $space): void
    {
        // Furniture
        $tableCategory = AssetCategory::where('name', 'Tables')->first();
        $chairCategory = AssetCategory::where('name', 'Chairs')->first();

        // Create conference table
        Asset::factory()
            ->forCategory($tableCategory)
            ->inSpace($space)
            ->create();

        // Create chairs
        Asset::factory()
            ->count(rand(6, 12))
            ->forCategory($chairCategory)
            ->inSpace($space)
            ->create();

        // IT Equipment
        $itCategory = AssetCategory::where('name', 'IT Equipment')->first();
        Asset::factory()
            ->count(rand(1, 3))
            ->forCategory($itCategory)
            ->inSpace($space)
            ->create();
    }

    private function createStorageAssets(Space $space): void
    {
        $storageCategory = AssetCategory::where('name', 'Storage')->first();
        
        // Create storage units
        Asset::factory()
            ->count(rand(5, 10))
            ->forCategory($storageCategory)
            ->inSpace($space)
            ->create();
    }

    private function createTechnicalAssets(Space $space): void
    {
        // Server room equipment
        $serverCategory = AssetCategory::where('name', 'Servers')->first();
        $networkingCategory = AssetCategory::where('name', 'Networking')->first();

        // Create servers
        Asset::factory()
            ->count(rand(3, 8))
            ->highCriticality()
            ->forCategory($serverCategory)
            ->inSpace($space)
            ->create();

        // Create networking equipment
        Asset::factory()
            ->count(rand(2, 5))
            ->highCriticality()
            ->forCategory($networkingCategory)
            ->inSpace($space)
            ->create();

        // Create HVAC equipment for server rooms
        $hvacCategory = AssetCategory::where('name', 'HVAC')->first();
        Asset::factory()
            ->count(2)
            ->highCriticality()
            ->forCategory($hvacCategory)
            ->inSpace($space)
            ->create();
    }

    private function createUtilityAssets(Space $space): void
    {
        // HVAC equipment
        $hvacCategory = AssetCategory::where('name', 'HVAC')->first();
        Asset::factory()
            ->count(rand(1, 3))
            ->forCategory($hvacCategory)
            ->inSpace($space)
            ->create();

        // Electrical equipment
        $electricalCategory = AssetCategory::where('name', 'Electrical')->first();
        Asset::factory()
            ->count(rand(1, 3))
            ->forCategory($electricalCategory)
            ->inSpace($space)
            ->create();

        // Plumbing equipment
        $plumbingCategory = AssetCategory::where('name', 'Plumbing')->first();
        Asset::factory()
            ->count(rand(1, 2))
            ->forCategory($plumbingCategory)
            ->inSpace($space)
            ->create();
    }

    private function createCommonAreaAssets(Space $space): void
    {
        // Furniture
        $furnitureCategory = AssetCategory::where('name', 'Furniture')->first();
        Asset::factory()
            ->count(rand(5, 10))
            ->forCategory($furnitureCategory)
            ->inSpace($space)
            ->create();

        // Safety equipment
        $safetyCategory = AssetCategory::where('name', 'Safety')->first();
        Asset::factory()
            ->count(rand(2, 4))
            ->forCategory($safetyCategory)
            ->inSpace($space)
            ->create();

        // HVAC equipment
        $hvacCategory = AssetCategory::where('name', 'HVAC')->first();
        Asset::factory()
            ->count(rand(1, 2))
            ->forCategory($hvacCategory)
            ->inSpace($space)
            ->create();
    }
} 