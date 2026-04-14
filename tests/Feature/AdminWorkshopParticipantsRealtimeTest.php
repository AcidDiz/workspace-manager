<?php

use App\Events\Workshop\AdminWorkshopParticipantsUpdated;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use App\Services\Workshop\WorkshopCancellationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Event;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

test('admin workshop show payload is broadcast when a participant is added', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $employee = User::factory()->create([
        'name' => 'Realtime Employee',
        'email' => 'realtime-employee@example.com',
    ]);
    $employee->assignRole('employee');

    $workshop = Workshop::factory()->upcoming()->create([
        'capacity' => 2,
        'created_by' => $admin->id,
    ]);

    Event::fake([AdminWorkshopParticipantsUpdated::class]);

    WorkshopRegistration::factory()->confirmed()->create([
        'workshop_id' => $workshop->id,
        'user_id' => $employee->id,
    ]);

    Event::assertDispatched(AdminWorkshopParticipantsUpdated::class, function (AdminWorkshopParticipantsUpdated $event) use ($workshop): bool {
        return $event->workshopId === $workshop->id
            && $event->state['workshop']['confirmed_registrations_count'] === 1
            && $event->state['workshop']['waiting_list_registrations_count'] === 0
            && $event->state['canAttachParticipants'] === true
            && count($event->state['participantList']) === 1
            && $event->state['participantList'][0]['registration_status'] === 'confirmed'
            && $event->state['participantList'][0]['user']['email'] === 'realtime-employee@example.com';
    });
});

test('admin workshop show payload is broadcast when a confirmed participant is removed and waiting list is promoted', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $holder = User::factory()->create();
    $holder->assignRole('employee');

    $waiter = User::factory()->create([
        'name' => 'Realtime Waiter',
        'email' => 'realtime-waiter@example.com',
    ]);
    $waiter->assignRole('employee');

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
        'user_id' => $waiter->id,
        'created_at' => now()->subMinutes(10),
    ]);

    Event::fake([AdminWorkshopParticipantsUpdated::class]);

    app(WorkshopCancellationService::class)->detach($holder, $workshop);

    Event::assertDispatched(AdminWorkshopParticipantsUpdated::class, function (AdminWorkshopParticipantsUpdated $event) use ($workshop): bool {
        return $event->workshopId === $workshop->id
            && $event->state['workshop']['confirmed_registrations_count'] === 1
            && $event->state['workshop']['waiting_list_registrations_count'] === 0
            && count($event->state['participantList']) === 1
            && $event->state['participantList'][0]['registration_status'] === 'confirmed'
            && $event->state['participantList'][0]['user']['email'] === 'realtime-waiter@example.com';
    });
});
