<?php

use App\Models\User;
use Database\Seeders\AcademyDemoSeeder;
use Database\Seeders\DatabaseSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('demo seed exposes upcoming workshops on the index for a demo user', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('workshops.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('workshops/Index')
            ->has('upcomingWorkshops', AcademyDemoSeeder::WORKSHOP_COUNT)
            ->where('upcomingWorkshops.0.title', 'Laravel in practice'));
});
