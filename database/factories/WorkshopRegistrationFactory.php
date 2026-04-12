<?php

namespace Database\Factories;

use App\Enums\WorkshopRegistrationStatus;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkshopRegistration>
 */
class WorkshopRegistrationFactory extends Factory
{
    protected $model = WorkshopRegistration::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_id' => Workshop::factory(),
            'user_id' => User::factory(),
            'status' => fake()->randomElement(WorkshopRegistrationStatus::cases()),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkshopRegistrationStatus::Confirmed,
        ]);
    }

    public function waitingList(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkshopRegistrationStatus::WaitingList,
        ]);
    }
}
