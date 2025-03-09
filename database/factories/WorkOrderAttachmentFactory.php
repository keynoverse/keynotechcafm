<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WorkOrderAttachmentFactory extends Factory
{
    protected $model = WorkOrderAttachment::class;

    public function definition(): array
    {
        $fileName = fake()->word() . '.' . fake()->randomElement(['pdf', 'jpg', 'png', 'doc', 'xlsx']);
        return [
            'work_order_id' => WorkOrder::factory(),
            'user_id' => User::factory(),
            'file_name' => $fileName,
            'file_path' => 'attachments/work-orders/' . date('Y/m/') . Str::random(40) . '/' . $fileName,
            'file_size' => fake()->numberBetween(1024, 5242880), // 1KB to 5MB
            'file_type' => fake()->randomElement(['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.ms-excel']),
            'description' => fake()->sentence(),
            'metadata' => [
                'uploaded_at' => now()->toDateTimeString(),
                'original_name' => $fileName,
                'mime_type' => fake()->mimeType(),
                'dimensions' => fake()->randomElement([
                    null,
                    ['width' => 1920, 'height' => 1080],
                    ['width' => 800, 'height' => 600],
                    ['width' => 1280, 'height' => 720]
                ]),
                'thumbnail_path' => fake()->boolean() ? 'thumbnails/work-orders/' . date('Y/m/') . Str::random(40) . '/thumb_' . $fileName : null,
                'processing_status' => fake()->randomElement(['pending', 'processing', 'completed', 'failed']),
                'tags' => fake()->randomElements([
                    'before',
                    'after',
                    'invoice',
                    'report',
                    'diagram',
                    'photo'
                ], rand(0, 3))
            ]
        ];
    }

    public function image(): static
    {
        $fileName = fake()->word() . '.' . fake()->randomElement(['jpg', 'png']);
        return $this->state(fn (array $attributes) => [
            'file_name' => $fileName,
            'file_type' => fake()->randomElement(['image/jpeg', 'image/png']),
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'dimensions' => [
                    'width' => fake()->numberBetween(800, 3840),
                    'height' => fake()->numberBetween(600, 2160)
                ],
                'thumbnail_path' => 'thumbnails/work-orders/' . date('Y/m/') . Str::random(40) . '/thumb_' . $fileName
            ])
        ]);
    }

    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_name' => fake()->word() . '.' . fake()->randomElement(['pdf', 'doc', 'docx']),
            'file_type' => fake()->randomElement(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'dimensions' => null,
                'page_count' => fake()->numberBetween(1, 50)
            ])
        ]);
    }

    public function spreadsheet(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_name' => fake()->word() . '.' . fake()->randomElement(['xls', 'xlsx']),
            'file_type' => fake()->randomElement(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'dimensions' => null,
                'sheet_count' => fake()->numberBetween(1, 10)
            ])
        ]);
    }

    public function forWorkOrder(WorkOrder $workOrder): static
    {
        return $this->state(fn (array $attributes) => [
            'work_order_id' => $workOrder->id
        ]);
    }

    public function uploadedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id
        ]);
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'processing_status' => 'completed',
                'processed_at' => now()->toDateTimeString()
            ])
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'processing_status' => 'failed',
                'error_message' => fake()->sentence(),
                'failed_at' => now()->toDateTimeString()
            ])
        ]);
    }
} 