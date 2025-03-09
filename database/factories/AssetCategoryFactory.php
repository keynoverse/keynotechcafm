<?php

namespace Database\Factories;

use App\Models\AssetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetCategoryFactory extends Factory
{
    protected $model = AssetCategory::class;

    public function definition(): array
    {
        static $categoryNumber = 1;

        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'metadata' => json_encode([
                'icon' => $this->faker->randomElement(['computer', 'printer', 'server', 'hvac', 'furniture', 'vehicle']),
                'color' => $this->faker->hexColor(),
                'maintenance_frequency' => $this->faker->randomElement(['weekly', 'monthly', 'quarterly', 'yearly']),
                'depreciation_rate' => $this->faker->randomFloat(2, 5, 25),
                'warranty_required' => $this->faker->boolean(),
                'requires_certification' => $this->faker->boolean(),
                'risk_level' => $this->faker->randomElement(['low', 'medium', 'high']),
                'asset_lifecycle' => $this->faker->randomElement(['3 years', '5 years', '8 years', '10 years'])
            ]),
            'parent_id' => null,
            '_lft' => 1,
            '_rgt' => 2
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