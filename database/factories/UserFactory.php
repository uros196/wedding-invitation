<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserType;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'user_type' => UserType::TeamMember,
            'team_id' => Team::factory()->withWedding(),
            'locale' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be verified.
     */
    public function verified(): static
    {
        return $this->state([
            'email_verified_at' => now(),
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
     * Indicate that the user is a management administrator without a team.
     */
    public function managementAdmin(): static
    {
        return $this->state([
            'user_type' => UserType::ManagementAdmin,
            'team_id' => null,
        ]);
    }

    /**
     * Indicate that the user belongs to a wedding team.
     */
    public function weddingTeamMember(): static
    {
        return $this->state([
            'user_type' => UserType::TeamMember,
            'team_id' => Team::factory()->withWedding(),
        ]);
    }
}
