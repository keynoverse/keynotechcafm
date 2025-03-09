<?php

namespace Database\Factories;

use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuildingFactory extends Factory
{
    protected $model = Building::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Building',
            'code' => 'BLD' . fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraph(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => fake()->country(),
            'postal_code' => fake()->postcode(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'total_floors' => fake()->numberBetween(1, 50),
            'total_area' => fake()->numberBetween(1000, 100000),
            'year_built' => fake()->year(),
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance', 'renovation']),
            'metadata' => [
                'construction_type' => fake()->randomElement(['Steel', 'Concrete', 'Wood Frame', 'Masonry']),
                'occupancy_type' => fake()->randomElement(['Commercial', 'Residential', 'Mixed Use', 'Industrial']),
                'facilities_manager' => [
                    'name' => fake()->name(),
                    'phone' => fake()->phoneNumber(),
                    'email' => fake()->email()
                ],
                'emergency_contacts' => [
                    [
                        'name' => fake()->name(),
                        'phone' => fake()->phoneNumber(),
                        'role' => 'Security'
                    ],
                    [
                        'name' => fake()->name(),
                        'phone' => fake()->phoneNumber(),
                        'role' => 'Maintenance'
                    ]
                ],
                'certifications' => fake()->randomElements([
                    'LEED',
                    'ENERGY STAR',
                    'BREEAM',
                    'WELL Building'
                ], rand(1, 3))
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
} 