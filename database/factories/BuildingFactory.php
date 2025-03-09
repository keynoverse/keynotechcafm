<?php

namespace Database\Factories;

use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuildingFactory extends Factory
{
    protected $model = Building::class;

    private static $buildingNumber = 1;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['active', 'inactive', 'maintenance']);
        
        return [
            'code' => 'BLD' . str_pad(static::$buildingNumber++, 3, '0', STR_PAD_LEFT),
            'name' => $this->faker->unique()->company(),
            'address' => $this->faker->address(),
            'status' => $status,
            'year_built' => $this->faker->numberBetween(1990, 2023),
            'total_area' => $this->faker->numberBetween(1000, 50000),
            'metadata' => [
                'emergency_contacts' => [
                    [
                        'name' => $this->faker->name(),
                        'position' => 'Facility Manager',
                        'phone' => $this->faker->phoneNumber(),
                        'email' => $this->faker->email(),
                    ],
                    [
                        'name' => $this->faker->name(),
                        'position' => 'Security Manager',
                        'phone' => $this->faker->phoneNumber(),
                        'email' => $this->faker->email(),
                    ],
                ],
                'certifications' => [
                    'LEED' => $this->faker->randomElement(['None', 'Certified', 'Silver', 'Gold', 'Platinum']),
                    'Energy Star' => $this->faker->boolean(70) ? $this->faker->numberBetween(75, 100) : null,
                ],
                'facilities' => [
                    'parking_spaces' => $this->faker->numberBetween(50, 500),
                    'elevators' => $this->faker->numberBetween(2, 8),
                    'loading_docks' => $this->faker->numberBetween(1, 4),
                    'security_desk' => $this->faker->boolean(90),
                ],
                'systems' => [
                    'hvac' => [
                        'type' => $this->faker->randomElement(['Central', 'Distributed', 'Hybrid']),
                        'last_maintenance' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                    ],
                    'fire_safety' => [
                        'sprinkler_system' => $this->faker->boolean(95),
                        'fire_alarm' => $this->faker->boolean(100),
                        'last_inspection' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
                    ],
                ],
            ],
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