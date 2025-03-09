<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Floor;
use Illuminate\Database\Seeder;

class FloorSeeder extends Seeder
{
    public function run(): void
    {
        // Get all buildings
        $buildings = Building::all();

        foreach ($buildings as $building) {
            // Create basement floors if building has more than 5 floors
            if ($building->total_floors > 5) {
                Floor::factory()
                    ->count(2)
                    ->sequence(
                        ['name' => 'Basement 2', 'level' => -2],
                        ['name' => 'Basement 1', 'level' => -1]
                    )
                    ->forBuilding($building)
                    ->create();
            }

            // Create ground floor
            Floor::factory()
                ->forBuilding($building)
                ->create([
                    'name' => 'Ground Floor',
                    'level' => 0,
                    'metadata' => [
                        'floor_type' => 'Mixed Use',
                        'facilities' => [
                            'Reception',
                            'Security Desk',
                            'Visitor Area',
                            'Cafeteria'
                        ]
                    ]
                ]);

            // Create regular floors
            $floorCount = $building->total_floors - 1; // Minus ground floor
            for ($i = 1; $i <= $floorCount; $i++) {
                Floor::factory()
                    ->forBuilding($building)
                    ->create([
                        'name' => ordinalNumber($i) . ' Floor',
                        'level' => $i
                    ]);
            }

            // Set some floors under maintenance or renovation
            if ($building->total_floors > 3) {
                // One floor under maintenance
                Floor::where('building_id', $building->id)
                    ->inRandomOrder()
                    ->first()
                    ->update(['status' => 'maintenance']);

                // One floor under renovation
                Floor::where('building_id', $building->id)
                    ->where('status', 'active')
                    ->inRandomOrder()
                    ->first()
                    ->update(['status' => 'renovation']);
            }
        }
    }

    /**
     * Get ordinal number (1st, 2nd, 3rd, etc.)
     */
    private function ordinalNumber(int $number): string
    {
        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
            return $number . 'th';
        } else {
            return $number . $ends[$number % 10];
        }
    }
} 