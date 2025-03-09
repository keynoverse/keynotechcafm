<?php

namespace Database\Factories;

use App\Models\WorkOrder;
use App\Models\User;
use App\Models\Asset;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    private static $workOrderNumber = 1;

    public function definition(): array
    {
        $code = 'WO' . str_pad(static::$workOrderNumber++, 6, '0', STR_PAD_LEFT);
        $status = $this->faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled', 'on_hold']);
        $priority = $this->faker->randomElement(['low', 'medium', 'high', 'urgent']);
        
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $dueDate = clone $startDate;
        $dueDate->modify('+' . $this->faker->numberBetween(1, 30) . ' days');
        
        $completedDate = null;
        if ($status === 'completed') {
            $completedDate = clone $startDate;
            $completedDate->modify('+' . $this->faker->numberBetween(1, 15) . ' days');
        }

        return [
            'code' => $code,
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['repair', 'maintenance', 'inspection', 'installation', 'replacement']),
            'priority' => $priority,
            'status' => $status,
            'requester_id' => User::factory(),
            'assignee_id' => User::factory(),
            'asset_id' => Asset::factory(),
            'space_id' => Space::factory(),
            'start_date' => $startDate,
            'due_date' => $dueDate,
            'completed_date' => $completedDate,
            'estimated_hours' => $this->faker->randomFloat(2, 0.5, 8),
            'actual_hours' => $status === 'completed' ? $this->faker->randomFloat(2, 0.5, 12) : null,
            'estimated_cost' => $this->faker->randomFloat(2, 50, 5000),
            'actual_cost' => $status === 'completed' ? $this->faker->randomFloat(2, 50, 7500) : null,
            'metadata' => json_encode([
                'category' => $this->faker->randomElement(['electrical', 'plumbing', 'hvac', 'structural', 'it', 'security']),
                'skills_required' => $this->faker->randomElements([
                    'electrical', 'plumbing', 'carpentry', 'hvac', 'it',
                    'painting', 'welding', 'masonry', 'security'
                ], $this->faker->numberBetween(1, 3)),
                'parts_required' => $this->faker->boolean(70) ? $this->faker->randomElements([
                    'screws', 'nails', 'pipes', 'wires', 'filters',
                    'bulbs', 'fuses', 'switches', 'valves'
                ], $this->faker->numberBetween(1, 4)) : [],
                'safety_requirements' => $this->faker->randomElements([
                    'safety_glasses', 'hard_hat', 'gloves', 'safety_shoes',
                    'ear_protection', 'face_mask', 'fall_protection'
                ], $this->faker->numberBetween(1, 4)),
                'documentation' => [
                    'manuals_required' => $this->faker->boolean(50),
                    'photos_required' => $this->faker->boolean(70),
                    'permits_required' => $this->faker->boolean(30)
                ],
                'follow_up' => [
                    'inspection_required' => $this->faker->boolean(60),
                    'testing_required' => $this->faker->boolean(50),
                    'training_required' => $this->faker->boolean(30)
                ]
            ])
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
            'assignee_id' => $user->id
        ]);
    }

    public function reportedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'requester_id' => $user->id
        ]);
    }
} 