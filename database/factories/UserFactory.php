<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'no_hp' => fake()->unique()->numerify('08##########'),
            'role' => 'user',
            'password' => Hash::make('password'),
        ];
    }

    /**
     * Indicate that the user has no password yet (aktivasi pertama).
     */
    public function tanpaPassword(): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin (no_hp harus = ADMIN_PHONE di TestCase).
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'no_hp' => '089999999999',
        ]);
    }
}
