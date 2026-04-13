<?php

namespace App\Http\Controllers\App\Workshops;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workshops\ListWorkshopsIndexRequest;
use App\Http\Resources\Workshop\WorkshopListItemResource;
use App\Support\Filters\Workshops\BuildWorkshopIndexData;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WorkshopIndexController extends Controller
{
    public function __invoke(ListWorkshopsIndexRequest $request, BuildWorkshopIndexData $builder): Response|RedirectResponse
    {
        $user = $request->user();
        assert($user !== null);

        if ($user->can('workshops.manage')) {
            return redirect()->route('admin.workshops.index', $request->query());
        }

        $data = $builder->handle($user, $request->validated());

        return Inertia::render('app/workshops/Index', [
            'workshopList' => WorkshopListItemResource::collection($data['workshops'])
                ->resolve($request),
            'filters' => $data['filters'],
            'showWorkshopTable' => $data['showWorkshopTable'],
            'workshopTableColumns' => $data['workshopTableColumns'],
            'employeeFilterFields' => $data['employeeFilterFields'],
        ]);
    }
}
