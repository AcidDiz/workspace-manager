<?php

namespace App\Services\Workshop;

use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use Illuminate\Support\Facades\DB;

class WorkshopCancellationService
{
    public function detach(User $user, Workshop $workshop): bool
    {
        return DB::transaction(function () use ($user, $workshop) {
            Workshop::query()->whereKey($workshop->id)->lockForUpdate()->firstOrFail();

            $registration = WorkshopRegistration::query()
                ->where('workshop_id', $workshop->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($registration === null) {
                return false;
            }

            $registration->delete();

            return true;
        });
    }
}
