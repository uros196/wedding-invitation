<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Wedding;
use App\Models\WeddingTimeline;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeddingTimeline>
 */
class WeddingTimelineFactory extends Factory
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
            'title' => $this->faker->sentence(3),
            'address' => $this->faker->address(),
            'time' => $this->faker->time('H:i'),
            'map_url' => $this->faker->url(),
            'is_visible' => true,
            'icon' => $this->faker->randomElement(LucideIcon::cases())->value,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the timeline item is visible to guests.
     */
    public function visible(): static
    {
        return $this->state([
            'is_visible' => true,
        ]);
    }

    /**
     * Indicate that the timeline item is hidden from guests.
     */
    public function hidden(): static
    {
        return $this->state([
            'is_visible' => false,
        ]);
    }

    /**
     * Set the item's position in the wedding timeline.
     */
    public function atSortOrder(int $sortOrder): static
    {
        return $this->state([
            'sort_order' => max(0, $sortOrder),
        ]);
    }
}
