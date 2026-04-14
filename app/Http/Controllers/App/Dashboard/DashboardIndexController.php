<?php

namespace App\Http\Controllers\App\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Workshop\WorkshopListItemResource;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;
use App\Support\Workshop\WorkshopWaitingListPositions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardIndexController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        assert($user !== null);

        $base = WorkshopRegistration::query()->where('user_id', $user->id);
        $registrations = (clone $base)
            ->with('workshop')
            ->get()
            ->filter(fn (WorkshopRegistration $registration) => $registration->workshop !== null)
            ->keyBy('workshop_id');
        $waitingListPositions = WorkshopWaitingListPositions::forUserRegistrations($registrations->values());

        $upcomingWorkshopIds = $registrations
            ->filter(fn (WorkshopRegistration $registration) => $registration->workshop?->starts_at->isFuture())
            ->keys();

        $completedWorkshopIds = $registrations
            ->filter(fn (WorkshopRegistration $registration) => $registration->status->value === 'confirmed')
            ->filter(fn (WorkshopRegistration $registration) => ! $registration->workshop?->starts_at->isFuture())
            ->keys();

        return Inertia::render('app/dashboard/Index', [
            'registrationSummary' => [
                'confirmed' => (clone $base)->confirmed()->count(),
                'waiting_list' => (clone $base)->waitingList()->count(),
            ],
            'upcomingRegistrations' => $this->resolveWorkshopList(
                $request,
                $this->loadWorkshops($upcomingWorkshopIds->all()),
                $registrations,
                $waitingListPositions
            ),
            'completedWorkshops' => $this->resolveWorkshopList(
                $request,
                $this->loadWorkshops($completedWorkshopIds->all()),
                $registrations,
                $waitingListPositions
            ),
        ]);
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, Workshop>
     */
    private function loadWorkshops(array $ids): Collection
    {
        if ($ids === []) {
            return new Collection;
        }

        return Workshop::query()
            ->whereIn('id', $ids)
            ->withIndexRelations()
            ->withConfirmedRegistrationCount()
            ->get()
            ->sortBy(fn (Workshop $workshop) => [$workshop->starts_at->getTimestamp(), $workshop->id])
            ->values();
    }

    /**
     * @param  Collection<int, Workshop>  $workshops
     * @param  \Illuminate\Support\Collection<int, WorkshopRegistration>  $registrations
     * @param  array<int, int>  $waitingListPositions
     * @return array<int, array<string, mixed>>
     */
    private function resolveWorkshopList(Request $request, Collection $workshops, \Illuminate\Support\Collection $registrations, array $waitingListPositions): array
    {
        return $workshops->map(function (Workshop $workshop) use ($request, $registrations, $waitingListPositions): array {
            $resource = new WorkshopListItemResource($workshop);
            $resource->myRegistrationStatus = $registrations->get($workshop->id)?->status;
            $resource->myWaitingListPosition = $waitingListPositions[$workshop->id] ?? null;

            return $resource->resolve($request);
        })->all();
    }
}
