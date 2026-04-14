<?php

use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

test('guests cannot access the app workshop dashboard', function () {
    $this->get(route('app.dashboard'))->assertRedirect(route('login'));
});

test('users without workshops.view cannot access the app workshop dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('app.dashboard'))
        ->assertForbidden();
});

test('employees see the app dashboard with registration summary', function () {
    $employee = User::factory()->create();
    $employee->assignRole('employee');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $upcomingWorkshop = Workshop::factory()->upcoming()->create([
        'created_by' => $admin->id,
    ]);

    $pastStart = now()->subDays(5)->startOfHour();
    $completedWorkshop = Workshop::factory()->create([
        'created_by' => $admin->id,
        'starts_at' => $pastStart,
        'ends_at' => $pastStart->copy()->addHours(2),
    ]);

    WorkshopRegistration::factory()
        ->confirmed()
        ->create([
            'workshop_id' => $upcomingWorkshop->id,
            'user_id' => $employee->id,
        ]);

    WorkshopRegistration::factory()
        ->confirmed()
        ->create([
            'workshop_id' => $completedWorkshop->id,
            'user_id' => $employee->id,
        ]);

    $this->actingAs($employee)
        ->get(route('app.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('app/dashboard/Index')
            ->where('registrationSummary.confirmed', 2)
            ->where('registrationSummary.waiting_list', 0)
            ->has('upcomingRegistrations', 1)
            ->where('upcomingRegistrations.0.id', $upcomingWorkshop->id)
            ->where('upcomingRegistrations.0.my_waiting_list_position', null)
            ->has('completedWorkshops', 1)
            ->where('completedWorkshops.0.id', $completedWorkshop->id));
});

test('employees see their waiting list position in the dashboard upcoming workshops', function () {
    $employee = User::factory()->create();
    $employee->assignRole('employee');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $upcomingWorkshop = Workshop::factory()->upcoming()->create([
        'capacity' => 1,
        'created_by' => $admin->id,
    ]);

    WorkshopRegistration::factory()->confirmed()->create([
        'workshop_id' => $upcomingWorkshop->id,
        'user_id' => User::factory()->create()->id,
    ]);

    WorkshopRegistration::factory()->waitingList()->create([
        'workshop_id' => $upcomingWorkshop->id,
        'user_id' => User::factory()->create()->id,
        'created_at' => now()->subMinutes(10),
    ]);

    WorkshopRegistration::factory()->waitingList()->create([
        'workshop_id' => $upcomingWorkshop->id,
        'user_id' => $employee->id,
        'created_at' => now()->subMinutes(5),
    ]);

    $this->actingAs($employee)
        ->get(route('app.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('app/dashboard/Index')
            ->where('registrationSummary.confirmed', 0)
            ->where('registrationSummary.waiting_list', 1)
            ->has('upcomingRegistrations', 1)
            ->where('upcomingRegistrations.0.id', $upcomingWorkshop->id)
            ->where('upcomingRegistrations.0.my_registration_status', 'waiting_list')
            ->where('upcomingRegistrations.0.my_waiting_list_position', 2));
});
