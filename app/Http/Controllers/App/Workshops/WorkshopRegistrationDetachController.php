<?php

namespace App\Http\Controllers\App\Workshops;

use App\Enums\Workshop\WorkshopRegistrationStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Workshop;
use App\Services\Workshop\WorkshopCancellationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WorkshopRegistrationDetachController extends Controller
{
    public function __construct(
        private WorkshopCancellationService $workshopCancellationService,
    ) {}

    public function __invoke(Request $request, Workshop $workshop): RedirectResponse
    {
        Gate::authorize('detachRegistration', $workshop);

        $user = $request->user();
        assert($user !== null);

        $result = $this->workshopCancellationService->detach($user, $workshop);

        if (! $result['removed']) {
            Inertia::flash('toast', [
                'type' => 'info',
                'message' => __('You were not registered for this workshop.'),
            ]);

            return back();
        }

        $message = $result['previous_status'] === WorkshopRegistrationStatusEnum::WaitingList
            ? __('You have left the waiting list for this workshop.')
            : __('Your registration has been cancelled.');

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $message,
        ]);

        return back();
    }
}
