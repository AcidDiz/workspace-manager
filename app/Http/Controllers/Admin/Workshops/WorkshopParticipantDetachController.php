<?php

namespace App\Http\Controllers\Admin\Workshops;

use App\Enums\Workshop\WorkshopRegistrationStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workshops\DetachWorkshopParticipantRequest;
use App\Models\User;
use App\Models\Workshop;
use App\Services\Workshop\WorkshopCancellationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class WorkshopParticipantDetachController extends Controller
{
    public function __construct(
        private WorkshopCancellationService $workshopCancellationService,
    ) {}

    public function __invoke(DetachWorkshopParticipantRequest $request, Workshop $workshop): RedirectResponse
    {
        $subject = User::query()->findOrFail((int) $request->validated('user_id'));

        $result = $this->workshopCancellationService->detach($subject, $workshop);

        if (! $result['removed']) {
            Inertia::flash('toast', [
                'type' => 'info',
                'message' => __('This user was not registered for this workshop.'),
            ]);

            return back();
        }

        $message = $result['previous_status'] === WorkshopRegistrationStatusEnum::WaitingList
            ? __('User removed from the waiting list.')
            : __('Participant removed. If the workshop had a queue, the next waiting user was promoted when applicable.');

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $message,
        ]);

        return back();
    }
}
