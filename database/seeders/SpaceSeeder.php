<?php

namespace Database\Seeders;

use App\Models\Floor;
use App\Models\Space;
use Illuminate\Database\Seeder;

class SpaceSeeder extends Seeder
{
    public function run(): void
    {
        // Get all floors
        $floors = Floor::all();

        foreach ($floors as $floor) {
            // Ground floor spaces
            if ($floor->level === 0) {
                // Reception area
                Space::factory()
                    ->forFloor($floor)
                    ->create([
                        'name' => 'Reception Area',
                        'code' => 'RCP' . $floor->building_id . '001',
                        'type' => 'common',
                        'area' => 100,
                        'capacity' => 20,
                        'status' => 'active'
                    ]);

                // Lobby
                Space::factory()
                    ->forFloor($floor)
                    ->create([
                        'name' => 'Main Lobby',
                        'code' => 'LBY' . $floor->building_id . '001',
                        'type' => 'common',
                        'area' => 150,
                        'capacity' => 50,
                        'status' => 'active'
                    ]);

                // Security office
                Space::factory()
                    ->forFloor($floor)
                    ->create([
                        'name' => 'Security Office',
                        'code' => 'SEC' . $floor->building_id . '001',
                        'type' => 'office',
                        'area' => 30,
                        'capacity' => 5,
                        'status' => 'active'
                    ]);

                // Cafeteria if building has more than 5 floors
                if ($floor->building->total_floors > 5) {
                    Space::factory()
                        ->forFloor($floor)
                        ->create([
                            'name' => 'Cafeteria',
                            'code' => 'CAF' . $floor->building_id . '001',
                            'type' => 'common',
                            'area' => 200,
                            'capacity' => 100,
                            'status' => 'active'
                        ]);
                }
            }
            // Basement spaces
            elseif ($floor->level < 0) {
                // Storage rooms
                Space::factory()
                    ->storage()
                    ->count(3)
                    ->forFloor($floor)
                    ->create();

                // Utility rooms
                Space::factory()
                    ->count(2)
                    ->forFloor($floor)
                    ->create([
                        'type' => 'utility',
                        'area' => 40,
                        'capacity' => 4
                    ]);

                // Server room if it's a main office building
                if ($floor->building->code === 'HQ001') {
                    Space::factory()
                        ->forFloor($floor)
                        ->create([
                            'name' => 'Server Room',
                            'code' => 'SRV' . $floor->building_id . '001',
                            'type' => 'technical',
                            'area' => 80,
                            'capacity' => 10,
                            'status' => 'active'
                        ]);
                }
            }
            // Regular floor spaces
            else {
                // Create offices
                Space::factory()
                    ->office()
                    ->count(6)
                    ->forFloor($floor)
                    ->create();

                // Create meeting rooms
                Space::factory()
                    ->meetingRoom()
                    ->count(2)
                    ->forFloor($floor)
                    ->create();

                // Create utility rooms
                Space::factory()
                    ->count(1)
                    ->forFloor($floor)
                    ->create([
                        'type' => 'utility',
                        'area' => 20,
                        'capacity' => 2
                    ]);

                // Create storage room
                Space::factory()
                    ->storage()
                    ->count(1)
                    ->forFloor($floor)
                    ->create();
            }

            // Set some spaces under maintenance or renovation
            if ($floor->status === 'active') {
                // One space under maintenance
                Space::where('floor_id', $floor->id)
                    ->inRandomOrder()
                    ->first()
                    ->update(['status' => 'maintenance']);

                // One space under renovation
                Space::where('floor_id', $floor->id)
                    ->where('status', 'active')
                    ->inRandomOrder()
                    ->first()
                    ->update(['status' => 'renovation']);
            }
        }
    }
} 