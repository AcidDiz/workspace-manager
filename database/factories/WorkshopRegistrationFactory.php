<?php

namespace Database\Factories;

use App\Enums\Workshop\WorkshopRegistrationStatusEnum;
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
            'status' => fake()->randomElement(WorkshopRegistrationStatusEnum::cases()),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkshopRegistrationStatusEnum::Confirmed,
        ]);
    }

    public function waitingList(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkshopRegistrationStatusEnum::WaitingList,
        ]);
    }
}
