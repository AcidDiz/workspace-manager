<?php

namespace App\Services\Workshop;

use App\Enums\Workshop\WorkshopRegistrationStatusEnum;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Illuminate\Support\Facades\DB;

class WorkshopCancellationService
{
    /**
     * @return array{removed: bool, previous_status: WorkshopRegistrationStatusEnum|null}
     */
    public function detach(User $user, Workshop $workshop): array
    {
        return DB::transaction(function () use ($user, $workshop) {
            $workshop = Workshop::query()->whereKey($workshop->id)->lockForUpdate()->firstOrFail();

            $registration = WorkshopRegistration::query()
                ->where('workshop_id', $workshop->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($registration === null) {
                return ['removed' => false, 'previous_status' => null];
            }

            $previousStatus = $registration->status;
            $registration->delete();

            if ($previousStatus === WorkshopRegistrationStatusEnum::Confirmed) {
                $this->promoteFirstWaitingListMember($workshop);
            }

            return ['removed' => true, 'previous_status' => $previousStatus];
        });
    }

    /**
     * Promote the longest-waiting user when a confirmed seat was freed.
     * Caller must hold a row lock on {@see Workshop} (e.g. inside this service's transaction).
     */
    private function promoteFirstWaitingListMember(Workshop $workshop): ?WorkshopRegistration
    {
        $confirmedCount = WorkshopRegistration::query()
            ->where('workshop_id', $workshop->id)
            ->where('status', WorkshopRegistrationStatusEnum::Confirmed)
            ->count();

        if ($confirmedCount >= $workshop->capacity) {
            return null;
        }

        $next = WorkshopRegistration::query()
            ->where('workshop_id', $workshop->id)
            ->waitingList()
            ->orderBy('created_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();

        if ($next === null) {
            return null;
        }

        $next->update([
            'status' => WorkshopRegistrationStatusEnum::Confirmed,
        ]);

        return $next->fresh();
    }
}
