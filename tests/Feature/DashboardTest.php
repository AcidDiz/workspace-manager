<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users without workshop access are forwarded to profile settings', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertRedirect(route('profile.edit'));
});

test('employees are forwarded from dashboard to the app home', function () {
    $employee = User::factory()->create();
    $employee->assignRole('employee');
    $this->actingAs($employee);

    $this->get(route('dashboard'))
        ->assertRedirect(route('app.dashboard'));
});
