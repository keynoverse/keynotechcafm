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
        $code = 'BLD' . str_pad(static::$buildingNumber++, 4, '0', STR_PAD_LEFT);
        
        return [
            'code' => $code,
            'name' => $this->faker->unique()->company() . ' Building',
            'description' => $this->faker->paragraph(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country' => $this->faker->country(),
            'postal_code' => $this->faker->postcode(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'under_maintenance', 'under_construction']),
            'year_built' => $this->faker->year(),
            'total_floors' => $this->faker->numberBetween(1, 50),
            'total_area' => $this->faker->numberBetween(1000, 100000),
            'occupancy_rate' => $this->faker->randomFloat(2, 0, 100),
            'metadata' => json_encode([
                'construction_type' => $this->faker->randomElement(['concrete', 'steel', 'wood', 'hybrid']),
                'building_class' => $this->faker->randomElement(['A', 'B', 'C']),
                'emergency_contacts' => [
                    'facility_manager' => [
                        'name' => $this->faker->name(),
                        'phone' => $this->faker->phoneNumber(),
                        'email' => $this->faker->email()
                    ],
                    'security' => [
                        'name' => $this->faker->name(),
                        'phone' => $this->faker->phoneNumber()
                    ]
                ],
                'certifications' => [
                    'leed' => $this->faker->randomElement(['platinum', 'gold', 'silver', 'certified', null]),
                    'energy_star' => $this->faker->boolean()
                ],
                'amenities' => $this->faker->randomElements([
                    'parking', 'cafeteria', 'gym', 'conference_rooms', 
                    'rooftop_garden', 'bike_storage', 'shower_facilities'
                ], $this->faker->numberBetween(2, 5))
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
} 