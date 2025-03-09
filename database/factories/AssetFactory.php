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
        static $assetNumber = 1;

        return [
            'category_id' => AssetCategory::factory(),
            'space_id' => Space::factory(),
            'name' => $this->faker->words(3, true),
            'code' => 'AST' . str_pad($assetNumber++, 4, '0', STR_PAD_LEFT),
            'description' => $this->faker->paragraph(),
            'model' => strtolower($this->faker->bothify('???-####')),
            'manufacturer' => $this->faker->company(),
            'serial_number' => 'SN-' . strtolower($this->faker->bothify('??????????##')),
            'purchase_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'purchase_cost' => $this->faker->randomFloat(2, 100, 10000),
            'warranty_expiry' => $this->faker->dateTimeBetween('now', '+5 years'),
            'maintenance_frequency' => $this->faker->numberBetween(30, 365),
            'maintenance_unit' => $this->faker->randomElement(['days', 'weeks', 'months']),
            'next_maintenance_date' => $this->faker->dateTimeBetween('now', '+2 years'),
            'status' => $this->faker->randomElement(['active', 'inactive', 'maintenance', 'repair']),
            'condition' => $this->faker->randomElement(['excellent', 'good', 'fair', 'poor']),
            'criticality' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'metadata' => json_encode([
                'specifications' => [
                    'dimensions' => $this->faker->numberBetween(20, 100) . 'x' . 
                                  $this->faker->numberBetween(20, 100) . 'x' . 
                                  $this->faker->numberBetween(20, 200) . 'cm',
                    'weight' => $this->faker->numberBetween(1, 100) . 'kg',
                    'power_requirements' => $this->faker->randomElement(['110V', '220V', '12V DC', 'N/A']),
                    'operating_temperature' => $this->faker->numberBetween(15, 30) . '°C'
                ],
                'warranty_info' => [
                    'provider' => $this->faker->company(),
                    'type' => $this->faker->randomElement(['Full', 'Limited', 'Parts Only']),
                    'contact' => $this->faker->phoneNumber()
                ],
                'maintenance_history' => [
                    'last_service' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                    'service_provider' => $this->faker->company(),
                    'total_downtime' => $this->faker->numberBetween(0, 100) . ' hours'
                ],
                'certifications' => $this->faker->randomElements([
                    'CE', 'UL Listed', 'Energy Star', 'ISO 9001', 'RoHS'
                ], $this->faker->numberBetween(1, 3)),
                'documents' => [
                    'manual_url' => $this->faker->url(),
                    'warranty_doc' => $this->faker->url(),
                    'service_history' => $this->faker->url()
                ]
            ])
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