<?php

use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Database\Seeders\AcademyDemoSeeder;
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

    expect(User::count())->toBe(16)
        ->and(User::role('employee')->count())->toBe(15);

    expect(Workshop::count())->toBe(AcademyDemoSeeder::WORKSHOP_COUNT);

    $expectedRegistrations = Workshop::query()->get()->sum(function (Workshop $workshop): int {
        return match (true) {
            $workshop->capacity >= 3 => 3,
            $workshop->capacity === 2 => 3,
            default => 2,
        };
    });

    $expectedConfirmed = Workshop::query()->get()->sum(function (Workshop $workshop): int {
        return match (true) {
            $workshop->capacity >= 3 => 3,
            $workshop->capacity === 2 => 2,
            default => 1,
        };
    });

    $expectedWaitingList = Workshop::query()->get()->sum(function (Workshop $workshop): int {
        return match (true) {
            $workshop->capacity >= 3 => 0,
            $workshop->capacity === 2 => 1,
            default => 1,
        };
    });

    expect(WorkshopRegistration::count())->toBe($expectedRegistrations)
        ->and(WorkshopRegistration::query()->confirmed()->count())->toBe($expectedConfirmed)
        ->and(WorkshopRegistration::query()->waitingList()->count())->toBe($expectedWaitingList);
});
