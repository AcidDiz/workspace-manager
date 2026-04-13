<?php

namespace App\Services\Workshop;

use App\Enums\Workshop\WorkshopRegistrationStatusEnum;
use App\Exceptions\Workshop\WorkshopRegistrationException;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Illuminate\Support\Facades\DB;

class WorkshopRegistrationService
{
    public function attach(User $user, Workshop $workshop): WorkshopRegistration
    {
        return DB::transaction(function () use ($user, $workshop) {
            $workshop = Workshop::query()->whereKey($workshop->id)->lockForUpdate()->firstOrFail();

            if (! $workshop->starts_at->isFuture()) {
                throw WorkshopRegistrationException::workshopClosed();
            }

            $existing = WorkshopRegistration::query()
                ->where('workshop_id', $workshop->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                throw WorkshopRegistrationException::alreadyRegistered();
            }

            $this->assertNoScheduleOverlapWithExistingRegistrations($user, $workshop, false);

            $confirmedCount = WorkshopRegistration::query()
                ->where('workshop_id', $workshop->id)
                ->where('status', WorkshopRegistrationStatusEnum::Confirmed)
                ->count();

            $status = $confirmedCount < $workshop->capacity
                ? WorkshopRegistrationStatusEnum::Confirmed
                : WorkshopRegistrationStatusEnum::WaitingList;

            return WorkshopRegistration::query()->create([
                'workshop_id' => $workshop->id,
                'user_id' => $user->id,
                'status' => $status,
            ]);
        });
    }

    /**
     * Admin-managed enrolment: any employee can be added even when the workshop is no longer “open”
     * for self-service; still enforces duplicate and schedule-overlap rules for the subject user.
     */
    public function attachAsAdmin(User $subject, Workshop $workshop): WorkshopRegistration
    {
        return DB::transaction(function () use ($subject, $workshop) {
            $workshop = Workshop::query()->whereKey($workshop->id)->lockForUpdate()->firstOrFail();

            $existing = WorkshopRegistration::query()
                ->where('workshop_id', $workshop->id)
                ->where('user_id', $subject->id)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                throw WorkshopRegistrationException::subjectAlreadyRegistered();
            }

            $this->assertNoScheduleOverlapWithExistingRegistrations($subject, $workshop, true);

            $confirmedCount = WorkshopRegistration::query()
                ->where('workshop_id', $workshop->id)
                ->where('status', WorkshopRegistrationStatusEnum::Confirmed)
                ->count();

            $status = $confirmedCount < $workshop->capacity
                ? WorkshopRegistrationStatusEnum::Confirmed
                : WorkshopRegistrationStatusEnum::WaitingList;

            return WorkshopRegistration::query()->create([
                'workshop_id' => $workshop->id,
                'user_id' => $subject->id,
                'status' => $status,
            ]);
        });
    }

    private function assertNoScheduleOverlapWithExistingRegistrations(User $user, Workshop $workshop, bool $adminSubject): void
    {
        $otherRegistrations = WorkshopRegistration::query()
            ->where('user_id', $user->id)
            ->where('workshop_id', '!=', $workshop->id)
            ->with('workshop')
            ->lockForUpdate()
            ->get();

        foreach ($otherRegistrations as $registration) {
            $otherWorkshop = $registration->workshop;
            if ($otherWorkshop === null) {
                continue;
            }

            if ($this->workshopIntervalsOverlap($workshop, $otherWorkshop)) {
                throw $adminSubject
                    ? WorkshopRegistrationException::subjectScheduleOverlap()
                    : WorkshopRegistrationException::scheduleOverlap();
            }
        }
    }

    /**
     * Standard interval overlap on [starts_at, ends_at).
     */
    private function workshopIntervalsOverlap(Workshop $a, Workshop $b): bool
    {
        return $a->starts_at->lt($b->ends_at) && $b->starts_at->lt($a->ends_at);
    }
}
