<?php

namespace Database\Factories;

use App\Models\Floor;
use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

class FloorFactory extends Factory
{
    protected $model = Floor::class;

    private static $floorNumber = 1;

    public function definition(): array
    {
        $code = 'FLR' . str_pad(static::$floorNumber++, 4, '0', STR_PAD_LEFT);
        
        return [
            'building_id' => Building::factory(),
            'code' => $code,
            'name' => $this->faker->unique()->words(3, true),
            'level' => $this->faker->numberBetween(-5, 100),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'under_maintenance', 'under_renovation']),
            'total_area' => $this->faker->numberBetween(500, 5000),
            'usable_area' => $this->faker->numberBetween(400, 4500),
            'common_area' => $this->faker->numberBetween(50, 500),
            'occupancy_rate' => $this->faker->randomFloat(2, 0, 100),
            'metadata' => json_encode([
                'floor_type' => $this->faker->randomElement(['office', 'retail', 'residential', 'mixed', 'parking', 'mechanical']),
                'max_occupancy' => $this->faker->numberBetween(50, 500),
                'emergency_exits' => $this->faker->numberBetween(2, 6),
                'facilities' => $this->faker->randomElements([
                    'restrooms', 'kitchen', 'conference_rooms', 'elevator_lobby', 
                    'storage', 'server_room', 'break_room', 'reception'
                ], $this->faker->numberBetween(3, 6)),
                'accessibility' => [
                    'wheelchair_accessible' => $this->faker->boolean(80),
                    'braille_signage' => $this->faker->boolean(70),
                    'hearing_loop' => $this->faker->boolean(50)
                ],
                'systems' => [
                    'hvac_zones' => $this->faker->numberBetween(2, 8),
                    'lighting_zones' => $this->faker->numberBetween(4, 12),
                    'security_cameras' => $this->faker->numberBetween(4, 16),
                    'access_points' => $this->faker->numberBetween(2, 6)
                ],
                'maintenance_schedule' => [
                    'cleaning' => $this->faker->randomElement(['daily', 'weekly']),
                    'inspection' => $this->faker->randomElement(['monthly', 'quarterly']),
                    'pest_control' => $this->faker->randomElement(['monthly', 'quarterly'])
                ]
            ])
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active'
        ]);
    }

    public function underMaintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance'
        ]);
    }

    public function underRenovation(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'renovation'
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive'
        ]);
    }

    public function forBuilding(Building $building): static
    {
        return $this->state(fn (array $attributes) => [
            'building_id' => $building->id
        ]);
    }
} 