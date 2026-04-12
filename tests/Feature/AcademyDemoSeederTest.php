<?php

use App\Enums\WorkshopRegistrationStatus;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Database\Seeders\DatabaseSeeder;
use Spatie\Permission\Models\Role;

test('academy demo seeder provisions roles, users, and workshop domain data', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Role::query()->orderBy('name')->pluck('name')->all())->toBe(['admin', 'employee']);

    $admin = User::query()->where('email', 'admin@example.com')->first();
    $employee = User::query()->where('email', 'employee@example.com')->first();

    expect($admin)->not->toBeNull()
        ->and($employee)->not->toBeNull()
        ->and($admin->hasRole('admin'))->toBeTrue()
        ->and($employee->hasRole('employee'))->toBeTrue();

    expect(Workshop::count())->toBe(3)
        ->and(WorkshopRegistration::count())->toBe(3);

    expect(WorkshopRegistration::query()->confirmed()->count())->toBe(2)
        ->and(WorkshopRegistration::query()->waitingList()->count())->toBe(1)
        ->and(
            WorkshopRegistration::query()
                ->where('status', WorkshopRegistrationStatus::WaitingList)
                ->value('user_id')
        )->toBe($employee->id);
});
