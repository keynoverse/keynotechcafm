<?php

namespace Database\Factories;

use App\Models\Floor;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpaceFactory extends Factory
{
    protected $model = Space::class;

    public function definition(): array
    {
        return [
            'floor_id' => Floor::factory(),
            'name' => fn (array $attributes) => fake()->randomElement([
                'Office',
                'Conference Room',
                'Meeting Room',
                'Break Room',
                'Storage Room',
                'Server Room',
                'Reception',
                'Open Space'
            ]) . ' ' . fake()->numberBetween(100, 999),
            'code' => fn (array $attributes) => 'SPC' . fake()->unique()->numberBetween(1000, 9999),
            'type' => fake()->randomElement([
                'office',
                'meeting',
                'storage',
                'common',
                'technical',
                'utility'
            ]),
            'description' => fake()->paragraph(),
            'area' => fake()->numberBetween(20, 200),
            'capacity' => fake()->numberBetween(1, 50),
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance', 'renovation']),
            'metadata' => [
                'features' => fake()->randomElements([
                    'Air Conditioning',
                    'Natural Light',
                    'Projector',
                    'TV Screen',
                    'Whiteboard',
                    'Network Points',
                    'Power Outlets',
                    'Phone Line'
                ], rand(3, 6)),
                'furniture' => [
                    'desks' => fake()->numberBetween(0, 10),
                    'chairs' => fake()->numberBetween(0, 20),
                    'cabinets' => fake()->numberBetween(0, 5)
                ],
                'environment' => [
                    'temperature' => fake()->numberBetween(20, 25),
                    'humidity' => fake()->numberBetween(40, 60),
                    'noise_level' => fake()->randomElement(['Low', 'Medium', 'High'])
                ],
                'access_control' => [
                    'type' => fake()->randomElement(['Card Access', 'Key', 'None']),
                    'restricted' => fake()->boolean(),
                    'hours' => fake()->randomElement(['24/7', '8AM-6PM', '7AM-7PM'])
                ],
                'maintenance' => [
                    'last_inspection' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                    'next_inspection' => fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
                    'cleaning_schedule' => fake()->randomElement(['Daily', 'Weekly', 'Monthly'])
                ]
            ]
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