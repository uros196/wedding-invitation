<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MemoryWallShare;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MemoryWallShare>
 */
class MemoryWallShareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wedding_id' => Wedding::factory(),
            'uuid' => Str::uuid()->toString(),
            'name' => $this->faker->words(3, true),
            'password' => null,
            'expires_at' => null,
            'allow_downloads' => false,
        ];
    }

    /**
     * Protect the generated share with a plain-text password that the model hashes.
     */
    public function passwordProtected(string $password = 'memory-wall-password'): static
    {
        return $this->state([
            'password' => $password,
        ]);
    }

    /**
     * Set an expiry in the past for unavailable-link tests.
     */
    public function expired(): static
    {
        return $this->state([
            'expires_at' => now()->subMinute(),
        ]);
    }
}
