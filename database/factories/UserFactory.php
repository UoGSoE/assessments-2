<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->regexify('[0-9]{7}[a-zA-Z]'),
            //'username' => fake()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'surname' => fake()->lastName(),
            'forenames' => fake()->firstName(),
            'email_verified_at' => now(),
            // TODO: Change back to hashed value after testing
            //'password' => static::$password ??= Hash::make('password'),
            'password' => 'secret',
            'is_student' => true,
            'is_staff' => false,
            'remember_token' => Str::random(10),
        ];
    }

    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'username' => fake()->unique()->regexify('[a-zA-Z]+[0-9]+[a-zA-Z]+'),
            'is_student' => false,
            'is_staff' => true,
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
}
