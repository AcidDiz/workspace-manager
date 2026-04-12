<?php

use App\Models\User;
use App\Models\Workshop;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected from the workshops index', function () {
    $this->get(route('workshops.index'))->assertRedirect(route('login'));
});

test('authenticated users can view the workshops index', function () {
    $user = User::factory()->create();

    Workshop::factory()->upcoming()->create([
        'title' => 'Visible session',
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('workshops.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('workshops/Index')
            ->has('upcomingWorkshops', 1)
            ->where('upcomingWorkshops.0.title', 'Visible session'));
});
