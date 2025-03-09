<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'category_id' => AssetCategory::factory(),
            'space_id' => Space::factory(),
            'name' => fake()->words(3, true),
            'code' => 'AST' . fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraph(),
            'model' => fake()->bothify('???-####'),
            'manufacturer' => fake()->company(),
            'serial_number' => fake()->unique()->bothify('SN-??????##??##'),
            'purchase_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'purchase_cost' => fake()->randomFloat(2, 100, 10000),
            'warranty_expiry' => fn (array $attributes) => fake()->dateTimeBetween($attributes['purchase_date'], '+5 years'),
            'maintenance_frequency' => fake()->numberBetween(30, 365),
            'maintenance_unit' => fake()->randomElement(['days', 'weeks', 'months', 'years']),
            'next_maintenance_date' => fake()->dateTimeBetween('now', '+1 year'),
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance', 'storage']),
            'condition' => fake()->randomElement(['excellent', 'good', 'fair', 'poor']),
            'criticality' => fake()->randomElement(['high', 'medium', 'low']),
            'metadata' => [
                'specifications' => [
                    'dimensions' => fake()->randomElement(['60x60x120cm', '80x80x150cm', '100x100x180cm']),
                    'weight' => fake()->numberBetween(5, 100) . 'kg',
                    'power_requirements' => fake()->randomElement(['110V', '220V', 'N/A']),
                    'operating_temperature' => fake()->numberBetween(15, 30) . '°C'
                ],
                'warranty_info' => [
                    'provider' => fake()->company(),
                    'type' => fake()->randomElement(['Full Coverage', 'Limited', 'Parts Only']),
                    'contact' => fake()->phoneNumber()
                ],
                'maintenance_history' => [
                    'last_service' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                    'service_provider' => fake()->company(),
                    'total_downtime' => fake()->numberBetween(0, 100) . ' hours'
                ],
                'certifications' => fake()->randomElements([
                    'ISO 9001',
                    'CE',
                    'UL Listed',
                    'Energy Star',
                    'RoHS'
                ], rand(1, 3)),
                'documents' => [
                    'manual_url' => fake()->url(),
                    'warranty_doc' => fake()->url(),
                    'service_history' => fake()->url()
                ]
            ]
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'condition' => fake()->randomElement(['excellent', 'good'])
        ]);
    }

    public function underMaintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
            'next_maintenance_date' => now()->addDays(fake()->numberBetween(1, 7))
        ]);
    }

    public function inStorage(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'storage',
            'space_id' => null
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'condition' => fake()->randomElement(['fair', 'poor'])
        ]);
    }

    public function forCategory(AssetCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id
        ]);
    }

    public function inSpace(Space $space): static
    {
        return $this->state(fn (array $attributes) => [
            'space_id' => $space->id
        ]);
    }

    public function highCriticality(): static
    {
        return $this->state(fn (array $attributes) => [
            'criticality' => 'high',
            'maintenance_frequency' => fake()->numberBetween(7, 30),
            'maintenance_unit' => 'days'
        ]);
    }

    public function lowCriticality(): static
    {
        return $this->state(fn (array $attributes) => [
            'criticality' => 'low',
            'maintenance_frequency' => fake()->numberBetween(180, 365),
            'maintenance_unit' => 'days'
        ]);
    }
} 