<?php

namespace Database\Factories;

use App\Models\Space;
use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpaceFactory extends Factory
{
    protected $model = Space::class;

    private static $spaceNumber = 1;

    public function definition(): array
    {
        $code = 'SPC' . str_pad(static::$spaceNumber++, 4, '0', STR_PAD_LEFT);
        
        return [
            'floor_id' => Floor::factory(),
            'code' => $code,
            'name' => $this->faker->unique()->words(3, true),
            'type' => $this->faker->randomElement(['office', 'meeting_room', 'storage', 'break_room', 'reception', 'server_room', 'utility']),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'under_maintenance', 'under_renovation']),
            'area' => $this->faker->numberBetween(20, 500),
            'capacity' => $this->faker->numberBetween(1, 50),
            'occupancy' => $this->faker->numberBetween(0, 50),
            'metadata' => json_encode([
                'features' => $this->faker->randomElements([
                    'windows', 'natural_light', 'climate_control', 'network_ports',
                    'projector', 'whiteboard', 'video_conferencing', 'phone_booth'
                ], $this->faker->numberBetween(2, 6)),
                'furniture' => [
                    'desks' => $this->faker->numberBetween(0, 20),
                    'chairs' => $this->faker->numberBetween(0, 40),
                    'cabinets' => $this->faker->numberBetween(0, 10),
                    'tables' => $this->faker->numberBetween(0, 5)
                ],
                'utilities' => [
                    'power_outlets' => $this->faker->numberBetween(4, 20),
                    'ethernet_ports' => $this->faker->numberBetween(2, 10),
                    'phone_lines' => $this->faker->numberBetween(0, 5)
                ],
                'environment' => [
                    'temperature_zone' => $this->faker->randomElement(['zone_a', 'zone_b', 'zone_c']),
                    'lighting_zone' => $this->faker->randomElement(['zone_1', 'zone_2', 'zone_3']),
                    'noise_level' => $this->faker->randomElement(['low', 'medium', 'high'])
                ],
                'access_control' => [
                    'type' => $this->faker->randomElement(['keycard', 'pin', 'biometric', 'key']),
                    'security_level' => $this->faker->randomElement(['low', 'medium', 'high']),
                    'restricted' => $this->faker->boolean(30)
                ],
                'scheduling' => [
                    'bookable' => $this->faker->boolean(70),
                    'min_booking_duration' => $this->faker->randomElement(['30m', '1h', '2h', '4h']),
                    'max_booking_duration' => $this->faker->randomElement(['4h', '8h', '1d'])
                ]
            ])
        ];
    }

    public function office(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'office',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'office_type' => fake()->randomElement(['Private', 'Shared', 'Open Plan']),
                'workstations' => fake()->numberBetween(1, 8)
            ])
        ]);
    }

    public function meetingRoom(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'meeting',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'meeting_room_type' => fake()->randomElement(['Conference', 'Huddle', 'Board Room']),
                'equipment' => [
                    'projector' => fake()->boolean(),
                    'video_conferencing' => fake()->boolean(),
                    'whiteboard' => fake()->boolean(),
                    'tv_screen' => fake()->boolean()
                ]
            ])
        ]);
    }

    public function storage(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'storage',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'storage_type' => fake()->randomElement(['General', 'Secure', 'Climate Controlled']),
                'shelving_units' => fake()->numberBetween(2, 10),
                'temperature_controlled' => fake()->boolean()
            ])
        ]);
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

    public function forFloor(Floor $floor): static
    {
        return $this->state(fn (array $attributes) => [
            'floor_id' => $floor->id
        ]);
    }
} 