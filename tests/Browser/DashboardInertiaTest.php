<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

test('user can log in and reach the app dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    visit(route('login'))
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('Log in')
        ->assertPathIs('/app/dashboard')
        ->assertTitle('Dashboard - Workshop Manager')
        ->assertNoJavaScriptErrors();
});
