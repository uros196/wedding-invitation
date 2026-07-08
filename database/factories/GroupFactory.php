<?php

namespace Database\Factories;

use App\Models\Group;
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
            'name' => $this->faker->lastName().' '.$this->faker->randomElement(['Porodica', 'Par']),
            'description' => $this->faker->sentence(),
            'is_sent' => false,
            'views_count' => 0,
        ];
    }
}
