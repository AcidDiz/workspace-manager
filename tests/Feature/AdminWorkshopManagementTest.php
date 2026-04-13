<?php

use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopCategory;
use App\Models\WorkshopRegistration;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(RolePermissionSeeder::class));

test('admin can create a workshop with valid payload', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $category = WorkshopCategory::factory()->create();

    $starts = now()->addWeek()->startOfHour();
    $ends = (clone $starts)->addHours(3);

    $this->actingAs($admin)
        ->post(route('admin.workshops.store'), [
            'title' => 'New academy session',
            'description' => 'Hands-on exercises.',
            'workshop_category_id' => (string) $category->id,
            'starts_at' => $starts->format('Y-m-d\TH:i'),
            'ends_at' => $ends->format('Y-m-d\TH:i'),
            'capacity' => 12,
        ])
        ->assertRedirect(route('admin.workshops.index'));

    $workshop = Workshop::query()->where('title', 'New academy session')->first();

    expect($workshop)->not->toBeNull()
        ->and($workshop->created_by)->toBe($admin->id)
        ->and($workshop->workshop_category_id)->toBe($category->id)
        ->and($workshop->capacity)->toBe(12);
});

test('store rejects empty title', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $starts = now()->addWeek()->startOfHour();
    $ends = (clone $starts)->addHours(2);

    $this->actingAs($admin)
        ->post(route('admin.workshops.store'), [
            'title' => '',
            'description' => null,
            'workshop_category_id' => null,
            'starts_at' => $starts->format('Y-m-d\TH:i'),
            'ends_at' => $ends->format('Y-m-d\TH:i'),
            'capacity' => 10,
        ])
        ->assertSessionHasErrors('title');
});

test('store rejects ends_at before starts_at', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $starts = now()->addWeek()->startOfHour();
    $ends = (clone $starts)->subHour();

    $this->actingAs($admin)
        ->post(route('admin.workshops.store'), [
            'title' => 'Valid title',
            'description' => null,
            'workshop_category_id' => null,
            'starts_at' => $starts->format('Y-m-d\TH:i'),
            'ends_at' => $ends->format('Y-m-d\TH:i'),
            'capacity' => 10,
        ])
        ->assertSessionHasErrors('ends_at');
});

test('store rejects non positive capacity', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $starts = now()->addWeek()->startOfHour();
    $ends = (clone $starts)->addHours(2);

    $this->actingAs($admin)
        ->post(route('admin.workshops.store'), [
            'title' => 'Valid title',
            'description' => null,
            'workshop_category_id' => null,
            'starts_at' => $starts->format('Y-m-d\TH:i'),
            'ends_at' => $ends->format('Y-m-d\TH:i'),
            'capacity' => 0,
        ])
        ->assertSessionHasErrors('capacity');
});

test('admin can update a workshop', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $workshop = Workshop::factory()->upcoming()->create([
        'title' => 'Old title',
        'created_by' => $admin->id,
    ]);

    $starts = $workshop->starts_at->copy()->addDay();
    $ends = $workshop->ends_at->copy()->addDay();

    $this->actingAs($admin)
        ->put(route('admin.workshops.update', $workshop), [
            'title' => 'Revised title',
            'description' => 'Updated notes.',
            'workshop_category_id' => null,
            'starts_at' => $starts->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
            'ends_at' => $ends->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
            'capacity' => 25,
        ])
        ->assertRedirect(route('admin.workshops.index'));

    $workshop->refresh();

    expect($workshop->title)->toBe('Revised title')
        ->and($workshop->capacity)->toBe(25)
        ->and($workshop->description)->toBe('Updated notes.');
});

test('deleting a workshop cascades registrations', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $workshop = Workshop::factory()->upcoming()->create([
        'created_by' => $admin->id,
    ]);

    WorkshopRegistration::factory()->confirmed()->create([
        'workshop_id' => $workshop->id,
    ]);

    expect(WorkshopRegistration::query()->where('workshop_id', $workshop->id)->count())->toBe(1);

    $this->actingAs($admin)
        ->delete(route('admin.workshops.destroy', $workshop))
        ->assertRedirect(route('admin.workshops.index'));

    expect(Workshop::query()->find($workshop->id))->toBeNull()
        ->and(WorkshopRegistration::query()->where('workshop_id', $workshop->id)->count())->toBe(0);
});

test('employee cannot access admin workshop management routes', function () {
    $employee = User::factory()->create();
    $employee->assignRole('employee');

    $workshop = Workshop::factory()->upcoming()->create([
        'created_by' => User::factory()->create()->id,
    ]);

    $this->actingAs($employee)
        ->get(route('admin.workshops.create'))
        ->assertForbidden();

    $this->actingAs($employee)
        ->post(route('admin.workshops.store'), [])
        ->assertForbidden();

    $this->actingAs($employee)
        ->get(route('admin.workshops.edit', $workshop))
        ->assertForbidden();

    $this->actingAs($employee)
        ->put(route('admin.workshops.update', $workshop), [])
        ->assertForbidden();

    $this->actingAs($employee)
        ->delete(route('admin.workshops.destroy', $workshop))
        ->assertForbidden();
});

test('admin create and edit pages render with categories', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    WorkshopCategory::factory()->create(['name' => 'Zeta track']);

    $workshop = Workshop::factory()->upcoming()->create([
        'created_by' => $admin->id,
        'workshop_category_id' => null,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.workshops.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/workshops/Create')
            ->has('categories', 1));

    $this->actingAs($admin)
        ->get(route('admin.workshops.edit', $workshop))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/workshops/Edit')
            ->has('workshop')
            ->where('workshop.id', $workshop->id));
});
