<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TeamType;
use App\Models\Team;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'has_memory_wall' => true,
            'type' => TeamType::Wedding,
        ];
    }

    /**
     * Indicate that the team has the memory wall enabled.
     */
    public function withMemoryWall(): static
    {
        return $this->state([
            'has_memory_wall' => true,
        ]);
    }

    /**
     * Indicate that the team has the memory wall disabled.
     */
    public function withoutMemoryWall(): static
    {
        return $this->state([
            'has_memory_wall' => false,
        ]);
    }

    /**
     * Create the wedding managed by the team.
     */
    public function withWedding(): static
    {
        return $this->has(Wedding::factory(), 'wedding');
    }
}
