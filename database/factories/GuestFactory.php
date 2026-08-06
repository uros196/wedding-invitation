<?php

declare(strict_types=1);

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
            'team_id' => function (array $attributes): ?int {
                $groupId = $attributes['group_id'] ?? null;

                if ($groupId instanceof Group) {
                    $groupId = $groupId->getKey();
                }

                if (blank($groupId)) {
                    return null;
                }

                /** @var Group|null $group */
                $group = Group::query()
                    ->with('wedding:id,team_id')
                    ->find($groupId);

                $teamId = $group?->wedding?->getAttribute('team_id');

                return filled($teamId) ? (int) $teamId : null;
            },
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'status' => GuestStatus::Pending,
            'age' => Age::Adult,
            'gender' => $this->faker->randomElement(Gender::cases()),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the guest has not responded to the invitation.
     */
    public function pending(): static
    {
        return $this->state([
            'status' => GuestStatus::Pending,
        ]);
    }

    /**
     * Indicate that the guest confirmed attendance.
     */
    public function confirmed(): static
    {
        return $this->state([
            'status' => GuestStatus::Confirmed,
        ]);
    }

    /**
     * Indicate that the guest declined attendance.
     */
    public function declined(): static
    {
        return $this->state([
            'status' => GuestStatus::Declined,
        ]);
    }

    /**
     * Set the guest's age category to adult.
     */
    public function adult(): static
    {
        return $this->state([
            'age' => Age::Adult,
        ]);
    }

    /**
     * Set the guest's age category to child.
     */
    public function child(): static
    {
        return $this->state([
            'age' => Age::Child,
        ]);
    }

    /**
     * Set the guest's age category to baby.
     */
    public function baby(): static
    {
        return $this->state([
            'age' => Age::Baby,
        ]);
    }

    /**
     * Set the guest's gender to male.
     */
    public function male(): static
    {
        return $this->state([
            'gender' => Gender::Male,
        ]);
    }

    /**
     * Set the guest's gender to female.
     */
    public function female(): static
    {
        return $this->state([
            'gender' => Gender::Female,
        ]);
    }

    /**
     * Indicate that the guest is a companion of the given parent guest.
     */
    public function companionOf(Guest $parent): static
    {
        return $this->state([
            'parent_id' => $parent->getKey(),
            'group_id' => $parent->group_id,
            'team_id' => $parent->team_id,
        ]);
    }
}
