<?php

use App\Events\Workshop\UserWorkshopRegistrationStateUpdated;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use App\Services\Workshop\WorkshopCancellationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Event;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

test('waiting list changes broadcast updated registration state to affected employee', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $employee = User::factory()->create();
    $employee->assignRole('employee');

    $workshop = Workshop::factory()->upcoming()->create([
        'capacity' => 1,
        'created_by' => $admin->id,
    ]);

    WorkshopRegistration::factory()->confirmed()->create([
        'workshop_id' => $workshop->id,
        'user_id' => User::factory()->create()->id,
    ]);

    WorkshopRegistration::factory()->waitingList()->create([
        'workshop_id' => $workshop->id,
        'user_id' => User::factory()->create()->id,
        'created_at' => now()->subMinutes(10),
    ]);

    Event::fake([UserWorkshopRegistrationStateUpdated::class]);

    WorkshopRegistration::factory()->waitingList()->create([
        'workshop_id' => $workshop->id,
        'user_id' => $employee->id,
        'created_at' => now()->subMinutes(5),
    ]);

    Event::assertDispatched(UserWorkshopRegistrationStateUpdated::class, function (UserWorkshopRegistrationStateUpdated $event) use ($employee, $workshop): bool {
        return $event->userId === $employee->id
            && $event->workshopId === $workshop->id
            && $event->registrationStatus === 'waiting_list'
            && $event->waitingListPosition === 2;
    });
});

test('cancelling a confirmed registration broadcasts promoted state to the first waiting employee', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $holder = User::factory()->create();
    $holder->assignRole('employee');

    $employee = User::factory()->create();
    $employee->assignRole('employee');

    $workshop = Workshop::factory()->upcoming()->create([
        'capacity' => 1,
        'created_by' => $admin->id,
    ]);

    WorkshopRegistration::factory()->confirmed()->create([
        'workshop_id' => $workshop->id,
        'user_id' => $holder->id,
    ]);

    WorkshopRegistration::factory()->waitingList()->create([
        'workshop_id' => $workshop->id,
        'user_id' => $employee->id,
        'created_at' => now()->subMinutes(10),
    ]);

    Event::fake([UserWorkshopRegistrationStateUpdated::class]);

    app(WorkshopCancellationService::class)->detach($holder, $workshop);

    Event::assertDispatched(UserWorkshopRegistrationStateUpdated::class, function (UserWorkshopRegistrationStateUpdated $event) use ($employee, $workshop): bool {
        return $event->userId === $employee->id
            && $event->workshopId === $workshop->id
            && $event->registrationStatus === 'confirmed'
            && $event->waitingListPosition === null;
    });
});
