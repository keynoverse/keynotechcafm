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
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'employee_id' => 'EMP' . $this->faker->unique()->numberBetween(1000, 9999),
            'department' => $this->faker->randomElement(['IT', 'Maintenance', 'Facilities', 'Finance', 'HR']),
            'position' => $this->faker->jobTitle(),
            'phone' => $this->faker->phoneNumber(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'metadata' => json_encode([
                'address' => $this->faker->address(),
                'emergency_contact' => [
                    'name' => $this->faker->name(),
                    'phone' => $this->faker->phoneNumber(),
                    'relationship' => $this->faker->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend'])
                ],
                'skills' => $this->faker->randomElements([
                    'HVAC', 'Plumbing', 'Electrical', 'Carpentry', 'IT', 'Security'
                ], $this->faker->numberBetween(1, 3)),
                'certifications' => $this->faker->randomElements([
                    'First Aid', 'OSHA', 'EPA', 'LEED', 'Security+'
                ], $this->faker->numberBetween(0, 2))
            ]),
            'role' => $this->faker->randomElement(['admin', 'manager', 'technician', 'user'])
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
