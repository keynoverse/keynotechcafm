<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'employee_id' => 'EMP' . fake()->unique()->numberBetween(1000, 9999),
            'department' => fake()->randomElement(['Maintenance', 'Facilities', 'IT', 'HR', 'Finance']),
            'position' => fake()->jobTitle(),
            'phone' => fake()->phoneNumber(),
            'status' => fake()->randomElement(['active', 'inactive']),
            'metadata' => [
                'address' => fake()->address(),
                'emergency_contact' => [
                    'name' => fake()->name(),
                    'phone' => fake()->phoneNumber(),
                    'relationship' => fake()->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend'])
                ],
                'skills' => fake()->randomElements([
                    'HVAC',
                    'Plumbing',
                    'Electrical',
                    'Carpentry',
                    'IT Support',
                    'Project Management'
                ], rand(1, 3))
            ]
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => 'Administrator',
            'department' => 'Administration',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'access_level' => 'admin',
                'permissions' => ['all']
            ])
        ]);
    }

    /**
     * Indicate that the user is a technician.
     */
    public function technician(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => 'Maintenance Technician',
            'department' => 'Maintenance',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'certifications' => [
                    fake()->randomElement(['HVAC', 'Electrical', 'Plumbing']),
                    fake()->randomElement(['Safety', 'First Aid', 'Equipment Operation'])
                ]
            ])
        ]);
    }

    /**
     * Indicate that the user is a supervisor.
     */
    public function supervisor(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => 'Maintenance Supervisor',
            'department' => 'Maintenance',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'team_size' => fake()->numberBetween(5, 15),
                'certifications' => [
                    'Project Management',
                    'Team Leadership',
                    fake()->randomElement(['HVAC', 'Electrical', 'Plumbing'])
                ]
            ])
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
