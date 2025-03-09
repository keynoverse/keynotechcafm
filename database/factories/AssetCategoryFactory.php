<?php

namespace Database\Factories;

use App\Models\AssetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetCategoryFactory extends Factory
{
    protected $model = AssetCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->paragraph(),
            'parent_id' => null,
            'status' => fake()->randomElement(['active', 'inactive']),
            'metadata' => [
                'icon' => fake()->randomElement(['building', 'desktop', 'server', 'chair', 'air-conditioner', 'printer']),
                'color' => fake()->hexColor(),
                'maintenance_frequency' => fake()->randomElement(['daily', 'weekly', 'monthly', 'quarterly', 'yearly']),
                'depreciation_rate' => fake()->randomFloat(2, 5, 25),
                'warranty_required' => fake()->boolean(),
                'requires_certification' => fake()->boolean(),
                'risk_level' => fake()->randomElement(['low', 'medium', 'high']),
                'asset_lifecycle' => fake()->numberBetween(1, 10) . ' years'
            ]
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active'
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive'
        ]);
    }

    public function asChild(AssetCategory $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id
        ]);
    }

    public function furniture(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Furniture',
            'description' => 'Office furniture and fixtures',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'icon' => 'chair',
                'maintenance_frequency' => 'yearly',
                'depreciation_rate' => 10.0,
                'warranty_required' => true,
                'requires_certification' => false,
                'risk_level' => 'low',
                'asset_lifecycle' => '7 years'
            ])
        ]);
    }

    public function itEquipment(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'IT Equipment',
            'description' => 'Computer hardware and networking equipment',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'icon' => 'desktop',
                'maintenance_frequency' => 'monthly',
                'depreciation_rate' => 25.0,
                'warranty_required' => true,
                'requires_certification' => true,
                'risk_level' => 'high',
                'asset_lifecycle' => '3 years'
            ])
        ]);
    }

    public function hvac(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'HVAC',
            'description' => 'Heating, ventilation, and air conditioning equipment',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'icon' => 'air-conditioner',
                'maintenance_frequency' => 'quarterly',
                'depreciation_rate' => 15.0,
                'warranty_required' => true,
                'requires_certification' => true,
                'risk_level' => 'medium',
                'asset_lifecycle' => '10 years'
            ])
        ]);
    }
} 