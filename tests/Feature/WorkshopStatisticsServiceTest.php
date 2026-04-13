<?php

use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use App\Services\Workshop\WorkshopStatisticsService;

test('statistics snapshot aggregates workshops and registrations', function () {
    $admin = User::factory()->create();
    $upcoming = Workshop::factory()->upcoming()->create(['created_by' => $admin->id]);

    $pastStart = now()->subDays(2);
    Workshop::factory()->create([
        'starts_at' => $pastStart,
        'ends_at' => (clone $pastStart)->addHours(3),
        'created_by' => $admin->id,
    ]);

    WorkshopRegistration::factory()->confirmed()->create(['workshop_id' => $upcoming->id]);
    WorkshopRegistration::factory()->waitingList()->create(['workshop_id' => $upcoming->id]);

    $snap = app(WorkshopStatisticsService::class)->snapshot();

    expect($snap['workshops']['total'])->toBe(2)
        ->and($snap['workshops']['upcoming'])->toBe(1)
        ->and($snap['workshops']['closed'])->toBe(1)
        ->and($snap['registrations']['confirmed'])->toBe(1)
        ->and($snap['registrations']['waiting_list'])->toBe(1)
        ->and($snap['registrations']['total'])->toBe(2)
        ->and($snap['popular_workshop'])->not->toBeNull()
        ->and($snap['popular_workshop']['id'])->toBe($upcoming->id);
});
