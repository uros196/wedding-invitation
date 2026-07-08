<?php

namespace Database\Factories;

use App\Enums\Age;
use App\Enums\Gender;
use App\Enums\GuestStatus;
use App\Models\Group;
use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guest>
 */
class GuestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'parent_id' => null,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'status' => GuestStatus::Pending,
            'age' => Age::Adult,
            'gender' => $this->faker->randomElement(Gender::cases()),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
