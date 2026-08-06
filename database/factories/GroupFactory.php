<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Group;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
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
            'wedding_id' => Wedding::factory(),
            'name' => $this->faker->lastName().' '.$this->faker->randomElement(['Porodica', 'Par']),
            'is_sent' => false,
            'has_plus_one' => false,
            'views_count' => 0,
            'invitation_title' => $this->faker->sentence(),
            'invitation_message' => $this->faker->paragraph(),
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    /**
     * Indicate that the invitation has been sent.
     */
    public function sent(): static
    {
        return $this->state([
            'is_sent' => true,
        ]);
    }

    /**
     * Indicate that the invitation has not been sent.
     */
    public function unsent(): static
    {
        return $this->state([
            'is_sent' => false,
        ]);
    }

    /**
     * Indicate that the group may add a plus-one guest.
     */
    public function withPlusOne(): static
    {
        return $this->state([
            'has_plus_one' => true,
        ]);
    }

    /**
     * Indicate that the group may not add a plus-one guest.
     */
    public function withoutPlusOne(): static
    {
        return $this->state([
            'has_plus_one' => false,
        ]);
    }

    /**
     * Set the number of invitation views for the group.
     */
    public function withViews(int $views = 1): static
    {
        return $this->state([
            'views_count' => max(0, $views),
        ]);
    }

    /**
     * Indicate that the group has custom meta-information.
     */
    public function withMeta(): static
    {
        return $this->state([
            'meta_title' => $this->faker->sentence(),
            'meta_description' => $this->faker->paragraph(),
        ]);
    }
}
