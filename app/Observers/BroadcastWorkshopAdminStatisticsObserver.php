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
        $this->broadcast();
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
