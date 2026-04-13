<?php

namespace App\Http\Controllers\App\Workshops;

use App\Enums\Workshop\WorkshopRegistrationStatusEnum;
use App\Exceptions\Workshop\WorkshopRegistrationException;
use App\Http\Controllers\Controller;
use App\Models\Workshop;
use App\Services\Workshop\WorkshopRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WorkshopRegistrationAttachController extends Controller
{
    public function __construct(
        private WorkshopRegistrationService $workshopRegistrationService,
    ) {}

    public function __invoke(Request $request, Workshop $workshop): RedirectResponse
    {
        Gate::authorize('attachRegistration', $workshop);

        $user = $request->user();
        assert($user !== null);

        try {
            $registration = $this->workshopRegistrationService->attach($user, $workshop);
        } catch (WorkshopRegistrationException $exception) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $exception->getMessage(),
            ]);

            return back();
        }

        $message = $registration->status === WorkshopRegistrationStatusEnum::WaitingList
            ? __('You have been added to the waiting list. We will notify you if a seat opens.')
            : __('You are registered for this workshop.');

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $message,
        ]);

        return back();
    }
}
