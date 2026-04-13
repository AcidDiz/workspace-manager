<?php

namespace App\Http\Controllers\Admin\Workshops;

use App\Enums\Workshop\WorkshopRegistrationStatusEnum;
use App\Exceptions\Workshop\WorkshopRegistrationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workshops\AttachWorkshopParticipantRequest;
use App\Models\User;
use App\Models\Workshop;
use App\Services\Workshop\WorkshopRegistrationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class WorkshopParticipantAttachController extends Controller
{
    public function __construct(
        private WorkshopRegistrationService $workshopRegistrationService,
    ) {}

    public function __invoke(AttachWorkshopParticipantRequest $request, Workshop $workshop): RedirectResponse
    {
        $subject = User::query()->findOrFail((int) $request->validated('user_id'));

        try {
            $registration = $this->workshopRegistrationService->attachAsAdmin($subject, $workshop);
        } catch (WorkshopRegistrationException $exception) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $exception->getMessage(),
            ]);

            return back();
        }

        $message = $registration->status === WorkshopRegistrationStatusEnum::WaitingList
            ? __('User added to the waiting list.')
            : __('User registered as a confirmed participant.');

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $message,
        ]);

        return back();
    }
}
