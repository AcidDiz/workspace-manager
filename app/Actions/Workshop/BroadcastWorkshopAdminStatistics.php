<?php

namespace App\Actions\Workshop;

use App\Events\Workshop\WorkshopAdminStatisticsUpdated;
use App\Services\Workshop\WorkshopStatisticsService;
use Illuminate\Support\Facades\DB;

class BroadcastWorkshopAdminStatistics
{
    public function __construct(
        private WorkshopStatisticsService $workshopStatisticsService,
    ) {}

    public function handle(): void
    {
        DB::afterCommit(function (): void {
            broadcast(new WorkshopAdminStatisticsUpdated(
                $this->workshopStatisticsService->snapshot(),
            ));
        });
    }
}
