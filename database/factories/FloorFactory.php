<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

class FloorFactory extends Factory
{
    protected $model = Floor::class;

    public function definition(): array
    {
        return [
            'building_id' => Building::factory(),
            'name' => fn (array $attributes) => fake()->randomElement(['Ground Floor', '1st Floor', '2nd Floor', '3rd Floor', 'Basement', 'Mezzanine']) . ' ' . fake()->numberBetween(1, 50),
            'code' => fn (array $attributes) => 'FLR' . fake()->unique()->numberBetween(1000, 9999),
            'level' => fake()->numberBetween(-2, 50),
            'description' => fake()->paragraph(),
            'total_area' => fake()->numberBetween(500, 5000),
            'common_area' => fake()->numberBetween(100, 1000),
            'rentable_area' => fn (array $attributes) => $attributes['total_area'] - $attributes['common_area'],
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance', 'renovation']),
            'metadata' => [
                'floor_type' => fake()->randomElement(['Office', 'Retail', 'Residential', 'Mixed Use', 'Parking']),
                'max_occupancy' => fake()->numberBetween(50, 500),
                'facilities' => fake()->randomElements([
                    'Restrooms',
                    'Kitchen',
                    'Conference Rooms',
                    'Break Room',
                    'Storage',
                    'Server Room',
                    'Elevator Access'
                ], rand(3, 5)),
                'emergency_equipment' => [
                    'fire_extinguishers' => fake()->numberBetween(2, 10),
                    'emergency_exits' => fake()->numberBetween(2, 4),
                    'first_aid_kits' => fake()->numberBetween(1, 3)
                ],
                'access_control' => [
                    'type' => fake()->randomElement(['Card Access', 'Biometric', 'Key Fob', 'None']),
                    'restricted' => fake()->boolean()
                ]
            ]
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