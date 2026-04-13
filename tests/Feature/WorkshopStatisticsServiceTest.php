<?php

use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use App\Services\Workshop\WorkshopStatisticsService;
use Illuminate\Support\Carbon;

test('snapshot returns zeroed aggregates when there is no data', function () {
    $now = Carbon::parse('2026-04-01 10:00:00', 'UTC');

    $service = app(WorkshopStatisticsService::class);
    $snapshot = $service->snapshot($now);

    expect($snapshot['workshops']['total'])->toBe(0)
        ->and($snapshot['workshops']['upcoming'])->toBe(0)
        ->and($snapshot['workshops']['closed'])->toBe(0)
        ->and($snapshot['registrations']['confirmed'])->toBe(0)
        ->and($snapshot['registrations']['waiting_list'])->toBe(0)
        ->and($snapshot['registrations']['total'])->toBe(0)
        ->and($snapshot['popular_workshop'])->toBeNull()
        ->and($snapshot['generated_at'])->toBe($now->toIso8601String());
});

test('snapshot counts workshops by timing and registrations by status', function () {
    $now = Carbon::parse('2026-06-15 12:00:00', 'UTC');
    $owner = User::factory()->create();

    Workshop::factory()->create([
        'starts_at' => $now->copy()->addDay(),
        'ends_at' => $now->copy()->addDay()->addHours(3),
        'created_by' => $owner->id,
    ]);
    Workshop::factory()->create([
        'starts_at' => $now->copy()->subDay(),
        'ends_at' => $now->copy()->subDay()->addHours(3),
        'created_by' => $owner->id,
    ]);

    $w1 = Workshop::factory()->create([
        'starts_at' => $now->copy()->addWeek(),
        'ends_at' => $now->copy()->addWeek()->addHours(2),
        'created_by' => $owner->id,
    ]);
    $w2 = Workshop::factory()->create([
        'starts_at' => $now->copy()->addWeeks(2),
        'ends_at' => $now->copy()->addWeeks(2)->addHours(2),
        'created_by' => $owner->id,
    ]);

    WorkshopRegistration::factory()->count(2)->confirmed()->create(['workshop_id' => $w1->id]);
    WorkshopRegistration::factory()->confirmed()->create(['workshop_id' => $w2->id]);
    WorkshopRegistration::factory()->waitingList()->create(['workshop_id' => $w2->id]);

    $service = app(WorkshopStatisticsService::class);
    $snapshot = $service->snapshot($now);

    expect($snapshot['workshops']['total'])->toBe(4)
        ->and($snapshot['workshops']['upcoming'])->toBe(3)
        ->and($snapshot['workshops']['closed'])->toBe(1)
        ->and($snapshot['registrations']['confirmed'])->toBe(3)
        ->and($snapshot['registrations']['waiting_list'])->toBe(1)
        ->and($snapshot['registrations']['total'])->toBe(4);
});

test('snapshot picks the workshop with the highest confirmed count as popular', function () {
    $now = Carbon::parse('2026-06-15 12:00:00', 'UTC');
    $owner = User::factory()->create();

    $quiet = Workshop::factory()->create([
        'title' => 'Quiet session',
        'starts_at' => $now->copy()->addDay(),
        'ends_at' => $now->copy()->addDay()->addHours(2),
        'created_by' => $owner->id,
    ]);
    $busy = Workshop::factory()->create([
        'title' => 'Busy session',
        'starts_at' => $now->copy()->addDays(2),
        'ends_at' => $now->copy()->addDays(2)->addHours(2),
        'created_by' => $owner->id,
    ]);

    WorkshopRegistration::factory()->confirmed()->create(['workshop_id' => $quiet->id]);
    WorkshopRegistration::factory()->count(3)->confirmed()->create(['workshop_id' => $busy->id]);

    $snapshot = app(WorkshopStatisticsService::class)->snapshot($now);

    expect($snapshot['popular_workshop'])->not->toBeNull()
        ->and($snapshot['popular_workshop']['id'])->toBe($busy->id)
        ->and($snapshot['popular_workshop']['title'])->toBe('Busy session')
        ->and($snapshot['popular_workshop']['confirmed_registrations_count'])->toBe(3);
});

test('snapshot breaks ties on popular workshop by ascending id', function () {
    $now = Carbon::parse('2026-06-15 12:00:00', 'UTC');
    $owner = User::factory()->create();

    $first = Workshop::factory()->create([
        'starts_at' => $now->copy()->addDay(),
        'ends_at' => $now->copy()->addDay()->addHours(2),
        'created_by' => $owner->id,
    ]);
    $second = Workshop::factory()->create([
        'starts_at' => $now->copy()->addDays(2),
        'ends_at' => $now->copy()->addDays(2)->addHours(2),
        'created_by' => $owner->id,
    ]);

    WorkshopRegistration::factory()->confirmed()->create(['workshop_id' => $first->id]);
    WorkshopRegistration::factory()->confirmed()->create(['workshop_id' => $second->id]);

    $snapshot = app(WorkshopStatisticsService::class)->snapshot($now);

    $expectedId = min($first->id, $second->id);

    expect($snapshot['popular_workshop']['id'])->toBe($expectedId);
});

test('snapshot omits popular workshop when no confirmed registrations exist', function () {
    $now = Carbon::parse('2026-06-15 12:00:00', 'UTC');
    $owner = User::factory()->create();

    $workshop = Workshop::factory()->create([
        'starts_at' => $now->copy()->addDay(),
        'ends_at' => $now->copy()->addDay()->addHours(2),
        'created_by' => $owner->id,
    ]);

    WorkshopRegistration::factory()->waitingList()->create([
        'workshop_id' => $workshop->id,
    ]);

    $snapshot = app(WorkshopStatisticsService::class)->snapshot($now);

    expect($snapshot['popular_workshop'])->toBeNull();
});
