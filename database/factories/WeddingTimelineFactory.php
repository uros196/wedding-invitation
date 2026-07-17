<?php

namespace Database\Factories;

use App\Models\Wedding;
use App\Models\WeddingTimeline;
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
            'icon' => 'heroicon-o-cake',
            'sort_order' => 0,
        ];
    }
}
