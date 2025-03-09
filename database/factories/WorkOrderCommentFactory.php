<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderComment;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkOrderCommentFactory extends Factory
{
    protected $model = WorkOrderComment::class;

    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'user_id' => User::factory(),
            'comment' => fake()->paragraph(),
            'type' => fake()->randomElement(['note', 'update', 'question', 'resolution']),
            'visibility' => fake()->randomElement(['public', 'private', 'internal']),
            'metadata' => [
                'timestamp' => now()->toDateTimeString(),
                'edited' => false,
                'attachments_count' => 0,
                'mentions' => [],
                'tags' => fake()->randomElements([
                    'important',
                    'follow-up',
                    'resolved',
                    'needs-attention',
                    'customer-update'
                ], rand(0, 2))
            ]
        ];
    }

    public function note(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'note',
            'comment' => 'Note: ' . fake()->sentence()
        ]);
    }

    public function update(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'update',
            'comment' => 'Update: ' . fake()->paragraph()
        ]);
    }

    public function question(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'question',
            'comment' => 'Question: ' . fake()->sentence() . '?'
        ]);
    }

    public function resolution(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'resolution',
            'comment' => 'Resolution: ' . fake()->paragraph()
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'private'
        ]);
    }

    public function internal(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'internal'
        ]);
    }

    public function forWorkOrder(WorkOrder $workOrder): static
    {
        return $this->state(fn (array $attributes) => [
            'work_order_id' => $workOrder->id
        ]);
    }

    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id
        ]);
    }

    public function withMentions(array $userIds): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'mentions' => $userIds
            ])
        ]);
    }

    public function edited(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'edited' => true,
                'last_edited_at' => now()->subMinutes(fake()->numberBetween(1, 60))->toDateTimeString(),
                'edit_count' => fake()->numberBetween(1, 5)
            ])
        ]);
    }
} 