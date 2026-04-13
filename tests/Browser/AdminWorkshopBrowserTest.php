<?php

use App\Models\User;
use App\Models\Workshop;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('admin can open the workshop create page when authenticated', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    visit(route('admin.workshops.create'))
        ->assertSee('Create workshop')
        ->assertNoJavaScriptErrors();
});

test('admin can edit a workshop from the admin table', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $workshop = Workshop::factory()->upcoming()->create([
        'title' => 'Original Browser Title',
        'created_by' => $admin->id,
        'workshop_category_id' => null,
    ]);

    $this->actingAs($admin);

    visit(route('admin.workshops.index'))
        ->assertSee('Original Browser Title')
        ->click('Edit')
        ->assertSee('Edit workshop')
        ->fill('#workshop-title', 'Updated Browser Title')
        ->click('Save changes')
        ->assertPathIs('/admin/workshops')
        ->assertSee('Updated Browser Title')
        ->assertNoJavaScriptErrors();

    expect(Workshop::query()->find($workshop->id)?->title)->toBe('Updated Browser Title');
});

test('admin can confirm delete workshop from the admin table', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $workshop = Workshop::factory()->upcoming()->create([
        'title' => 'Workshop To Remove',
        'created_by' => $admin->id,
        'workshop_category_id' => null,
    ]);

    $this->actingAs($admin);

    visit(route('admin.workshops.index'))
        ->assertSee('Workshop To Remove')
        ->click("@delete-workshop-{$workshop->id}")
        ->assertSee('Delete workshop')
        ->assertSee('Workshop To Remove')
        ->assertSee('This will remove')
        ->click('@confirm-delete-workshop-button')
        ->assertPathIs('/admin/workshops')
        ->assertNoJavaScriptErrors();

    visit(route('admin.workshops.index'))
        ->assertDontSee('Workshop To Remove')
        ->assertNoJavaScriptErrors();

    expect(Workshop::query()->find($workshop->id))->toBeNull();
});
