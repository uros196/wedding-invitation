<?php

namespace Database\Factories;

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
            'bride_name' => $this->faker->firstName('female'),
            'groom_name' => $this->faker->firstName('male'),
            'wedding_date' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
            'rsvp_deadline' => $this->faker->dateTimeBetween('now', '+1 month'),
            'welcome_text' => $this->faker->paragraph(),
            'meta_title' => $this->faker->sentence(),
            'meta_description' => $this->faker->sentence(),
        ];
    }
}
