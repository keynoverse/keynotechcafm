<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceScheduleFactory extends Factory
{
    protected $model = MaintenanceSchedule::class;

    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'scheduled_date' => fake()->dateTimeBetween('now', '+6 months'),
            'frequency' => fake()->numberBetween(1, 12),
            'frequency_unit' => fake()->randomElement(['days', 'weeks', 'months', 'years']),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'status' => fake()->randomElement(['scheduled', 'completed', 'cancelled', 'overdue']),
            'assigned_to' => User::factory(),
            'completion_date' => null,
            'completion_notes' => null,
            'completed_by' => null,
            'notes' => fake()->paragraph(),
            'metadata' => [
                'estimated_duration' => fake()->numberBetween(30, 480),
                'required_tools' => fake()->randomElements([
                    'Screwdriver Set',
                    'Multimeter',
                    'Wrench Set',
                    'Ladder',
                    'Safety Equipment',
                    'Diagnostic Tools'
                ], rand(2, 4)),
                'parts_needed' => fake()->randomElements([
                    'Filters',
                    'Belts',
                    'Lubricants',
                    'Gaskets',
                    'Screws',
                    'Fuses'
                ], rand(1, 3)),
                'safety_requirements' => fake()->randomElements([
                    'Safety Glasses',
                    'Hard Hat',
                    'Gloves',
                    'Steel-Toed Boots',
                    'High-Vis Vest'
                ], rand(1, 3)),
                'procedures' => [
                    'pre_check' => fake()->randomElements([
                        'Power off equipment',
                        'Check safety equipment',
                        'Clear work area',
                        'Review manual'
                    ], rand(2, 4)),
                    'post_check' => fake()->randomElements([
                        'Test operation',
                        'Clean work area',
                        'Document changes',
                        'Update logs'
                    ], rand(2, 4))
                ]
            ]
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'completion_date' => null,
            'completion_notes' => null,
            'completed_by' => null
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completion_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'completion_notes' => fake()->paragraph(),
            'completed_by' => User::factory()
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'notes' => 'Cancelled: ' . fake()->sentence()
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'scheduled_date' => fake()->dateTimeBetween('-3 months', '-1 day')
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
            'scheduled_date' => fake()->dateTimeBetween('now', '+1 week')
        ]);
    }

    public function forAsset(Asset $asset): static
    {
        return $this->state(fn (array $attributes) => [
            'asset_id' => $asset->id
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user->id
        ]);
    }
} 