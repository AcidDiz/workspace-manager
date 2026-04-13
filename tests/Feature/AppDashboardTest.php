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

    $workshop = Workshop::factory()->upcoming()->create([
        'created_by' => $admin->id,
    ]);

    WorkshopRegistration::factory()
        ->confirmed()
        ->create([
            'workshop_id' => $workshop->id,
            'user_id' => $employee->id,
        ]);

    $this->actingAs($employee)
        ->get(route('app.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('app/dashboard/Index')
            ->where('registrationSummary.confirmed', 1)
            ->where('registrationSummary.waiting_list', 0));
});
