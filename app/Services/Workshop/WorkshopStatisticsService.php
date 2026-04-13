<?php

namespace App\Services\Workshop;

use App\Enums\Workshop\WorkshopRegistrationStatusEnum;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Illuminate\Support\Carbon;

class WorkshopStatisticsService
{
    /**
     * Compact snapshot for the admin dashboard and JSON polling endpoint.
     *
     * @return array{
     *     workshops: array{total: int, upcoming: int, closed: int},
     *     registrations: array{confirmed: int, waiting_list: int, total: int},
     *     popular_workshop: null|array{id: int, title: string, confirmed_registrations_count: int},
     *     generated_at: string
     * }
     */
    public function snapshot(?Carbon $now = null): array
    {
        $now ??= now();

        $totalWorkshops = Workshop::query()->count();
        $upcomingWorkshops = Workshop::query()->where('starts_at', '>', $now)->count();
        $closedWorkshops = Workshop::query()->where('starts_at', '<=', $now)->count();

        $confirmedRegistrations = WorkshopRegistration::query()
            ->where('status', WorkshopRegistrationStatusEnum::Confirmed)
            ->count();

        $waitingListRegistrations = WorkshopRegistration::query()
            ->where('status', WorkshopRegistrationStatusEnum::WaitingList)
            ->count();

        $popularWorkshop = Workshop::query()
            ->withConfirmedRegistrationCount()
            ->orderByDesc('confirmed_registrations_count')
            ->orderBy('id')
            ->first();

        $popularPayload = null;
        if ($popularWorkshop !== null && $popularWorkshop->confirmed_registrations_count > 0) {
            $popularPayload = [
                'id' => $popularWorkshop->id,
                'title' => $popularWorkshop->title,
                'confirmed_registrations_count' => (int) $popularWorkshop->confirmed_registrations_count,
            ];
        }

        return [
            'workshops' => [
                'total' => $totalWorkshops,
                'upcoming' => $upcomingWorkshops,
                'closed' => $closedWorkshops,
            ],
            'registrations' => [
                'confirmed' => $confirmedRegistrations,
                'waiting_list' => $waitingListRegistrations,
                'total' => $confirmedRegistrations + $waitingListRegistrations,
            ],
            'popular_workshop' => $popularPayload,
            'generated_at' => $now->toIso8601String(),
        ];
    }
}
