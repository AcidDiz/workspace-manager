<?php

namespace App\Observers;

use App\Actions\Workshop\BroadcastWorkshopAdminStatistics;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;

class BroadcastWorkshopAdminStatisticsObserver
{
    public function __construct(
        private BroadcastWorkshopAdminStatistics $broadcastWorkshopAdminStatistics,
    ) {}

    public function created(Workshop|WorkshopRegistration $model): void
    {
        $this->broadcast();
    }

    public function updated(Workshop|WorkshopRegistration $model): void
    {
        if (! $this->shouldBroadcastAfterUpdate($model)) {
            return;
        }

        $this->broadcast();
    }

    private function shouldBroadcastAfterUpdate(Workshop|WorkshopRegistration $model): bool
    {
        if ($model instanceof Workshop) {
            return $model->wasChanged(['starts_at', 'ends_at', 'title']);
        }

        return $model->wasChanged(['status', 'workshop_id', 'user_id']);
    }

    public function deleted(Workshop|WorkshopRegistration $model): void
    {
        $this->broadcast();
    }

    private function broadcast(): void
    {
        $this->broadcastWorkshopAdminStatistics->handle();
    }
}
