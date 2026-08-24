<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Team;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wedding>
 */
class WeddingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'team_id' => Team::factory(),
            'status' => Status::Published,
            'bride_name' => $this->faker->firstName('female'),
            'groom_name' => $this->faker->firstName('male'),
            'wedding_date' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
            'rsvp_deadline' => $this->faker->dateTimeBetween('now', '+1 month'),
            'welcome_text' => $this->faker->paragraph(),
            'has_memory_wall' => true,
            'memory_wall_open_until' => null,
            'meta_title' => $this->faker->sentence(),
            'meta_description' => $this->faker->paragraph(),
        ];
    }

    /**
     * Indicate that RSVP submissions are currently open.
     */
    public function rsvpOpen(): static
    {
        return $this->state([
            'rsvp_deadline' => now()->addWeek(),
        ]);
    }

    /**
     * Indicate that RSVP submissions are closed.
     */
    public function rsvpClosed(): static
    {
        return $this->state([
            'rsvp_deadline' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the memory wall is enabled.
     */
    public function memoryWallEnabled(): static
    {
        return $this->state([
            'has_memory_wall' => true,
        ]);
    }

    /**
     * Indicate that the memory wall is disabled.
     */
    public function memoryWallDisabled(): static
    {
        return $this->state([
            'has_memory_wall' => false,
            'memory_wall_open_until' => null,
        ]);
    }
}
