<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Space;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'asset_id' => Asset::factory(),
            'space_id' => Space::factory(),
            'reported_by' => User::factory(),
            'assigned_to' => User::factory(),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'status' => fake()->randomElement(['open', 'in_progress', 'on_hold', 'completed', 'cancelled']),
            'type' => fake()->randomElement(['repair', 'maintenance', 'installation', 'inspection', 'emergency']),
            'due_date' => fake()->dateTimeBetween('now', '+2 weeks'),
            'start_date' => null,
            'completion_date' => null,
            'estimated_hours' => fake()->randomFloat(2, 1, 48),
            'actual_hours' => null,
            'cost_estimate' => fake()->randomFloat(2, 100, 5000),
            'actual_cost' => null,
            'metadata' => [
                'category' => fake()->randomElement(['electrical', 'plumbing', 'hvac', 'structural', 'it']),
                'location_details' => fake()->text(100),
                'access_requirements' => fake()->randomElements([
                    'Key required',
                    'Security clearance',
                    'Escort needed',
                    'After hours only'
                ], rand(1, 3)),
                'safety_requirements' => fake()->randomElements([
                    'PPE required',
                    'Lock out/Tag out',
                    'Confined space',
                    'Hot work permit'
                ], rand(1, 3)),
                'parts_required' => fake()->randomElements([
                    'Filters',
                    'Belts',
                    'Motors',
                    'Switches',
                    'Valves'
                ], rand(0, 3)),
                'tools_required' => fake()->randomElements([
                    'Hand tools',
                    'Power tools',
                    'Diagnostic equipment',
                    'Ladder',
                    'Lift'
                ], rand(1, 4)),
                'impact_assessment' => [
                    'severity' => fake()->randomElement(['low', 'medium', 'high']),
                    'affected_users' => fake()->numberBetween(1, 100),
                    'business_impact' => fake()->randomElement(['minimal', 'moderate', 'significant'])
                ]
            ]
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
            'start_date' => null,
            'completion_date' => null
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'start_date' => fake()->dateTimeBetween('-1 week', 'now'),
            'completion_date' => null
        ]);
    }

    public function completed(): static
    {
        $startDate = fake()->dateTimeBetween('-2 weeks', '-1 day');
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'start_date' => $startDate,
            'completion_date' => fake()->dateTimeBetween($startDate, 'now'),
            'actual_hours' => fake()->randomFloat(2, 1, 48),
            'actual_cost' => fake()->randomFloat(2, 100, 5000)
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'cancellation_reason' => fake()->sentence(),
                'cancelled_by' => fake()->name(),
                'cancelled_at' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d H:i:s')
            ])
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
            'due_date' => fake()->dateTimeBetween('now', '+2 days')
        ]);
    }

    public function forAsset(Asset $asset): static
    {
        return $this->state(fn (array $attributes) => [
            'asset_id' => $asset->id
        ]);
    }

    public function forSpace(Space $space): static
    {
        return $this->state(fn (array $attributes) => [
            'space_id' => $space->id
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user->id
        ]);
    }

    public function reportedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'reported_by' => $user->id
        ]);
    }
} 