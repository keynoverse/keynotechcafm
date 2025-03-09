<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\MaintenanceLog;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceLogFactory extends Factory
{
    protected $model = MaintenanceLog::class;

    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'schedule_id' => MaintenanceSchedule::factory(),
            'type' => fake()->randomElement(['preventive', 'corrective', 'emergency']),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'performed_by' => User::factory(),
            'performed_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'duration' => fake()->numberBetween(15, 480),
            'duration_unit' => fake()->randomElement(['minutes', 'hours']),
            'cost' => fake()->randomFloat(2, 50, 5000),
            'parts_used' => [
                [
                    'name' => fake()->word(),
                    'quantity' => fake()->numberBetween(1, 10),
                    'cost' => fake()->randomFloat(2, 10, 500)
                ],
                [
                    'name' => fake()->word(),
                    'quantity' => fake()->numberBetween(1, 10),
                    'cost' => fake()->randomFloat(2, 10, 500)
                ]
            ],
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            'notes' => fake()->paragraph(),
            'metadata' => [
                'technician_notes' => fake()->paragraph(),
                'issues_found' => fake()->randomElements([
                    'Worn parts',
                    'Loose connections',
                    'Unusual noise',
                    'Performance degradation',
                    'Leakage'
                ], rand(0, 3)),
                'actions_taken' => fake()->randomElements([
                    'Parts replacement',
                    'Cleaning',
                    'Adjustment',
                    'Lubrication',
                    'Testing'
                ], rand(1, 4)),
                'follow_up_required' => fake()->boolean(),
                'environmental_conditions' => [
                    'temperature' => fake()->numberBetween(18, 30),
                    'humidity' => fake()->numberBetween(30, 70),
                    'weather' => fake()->randomElement(['Clear', 'Rainy', 'Cloudy', 'Hot', 'Cold'])
                ],
                'safety_measures' => fake()->randomElements([
                    'Lock out/Tag out',
                    'PPE used',
                    'Area cordoned',
                    'Ventilation provided'
                ], rand(1, 4)),
                'quality_checks' => [
                    'performed' => fake()->boolean(),
                    'passed' => fake()->boolean(),
                    'checked_by' => fake()->name()
                ]
            ]
        ];
    }

    public function preventive(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'preventive',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'checklist_completed' => true,
                'next_maintenance_due' => fake()->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d')
            ])
        ]);
    }

    public function corrective(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'corrective',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'issue_reported_by' => fake()->name(),
                'issue_reported_date' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d'),
                'downtime' => fake()->numberBetween(1, 48) . ' hours'
            ])
        ]);
    }

    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'emergency',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'emergency_level' => fake()->randomElement(['Critical', 'High', 'Medium']),
                'response_time' => fake()->numberBetween(15, 120) . ' minutes',
                'impact_assessment' => fake()->paragraph()
            ])
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'completion_verification' => [
                    'verified_by' => fake()->name(),
                    'verification_date' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d'),
                    'comments' => fake()->sentence()
                ]
            ])
        ]);
    }

    public function forAsset(Asset $asset): static
    {
        return $this->state(fn (array $attributes) => [
            'asset_id' => $asset->id
        ]);
    }

    public function forSchedule(MaintenanceSchedule $schedule): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_id' => $schedule->id,
            'asset_id' => $schedule->asset_id
        ]);
    }

    public function performedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'performed_by' => $user->id
        ]);
    }
} 