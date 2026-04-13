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

            $confirmedCount = WorkshopRegistration::query()
                ->where('workshop_id', $workshop->id)
                ->where('status', WorkshopRegistrationStatusEnum::Confirmed)
                ->count();

            if ($confirmedCount >= $workshop->capacity) {
                throw WorkshopRegistrationException::full();
            }

            return WorkshopRegistration::query()->create([
                'workshop_id' => $workshop->id,
                'user_id' => $user->id,
                'status' => WorkshopRegistrationStatusEnum::Confirmed,
            ]);
        });
    }
}
