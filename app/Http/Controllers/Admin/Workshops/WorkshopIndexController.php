<?php

namespace App\Http\Controllers\Admin\Workshops;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workshops\ListWorkshopsIndexRequest;
use App\Http\Resources\Workshop\WorkshopListItemResource;
use App\Support\Filters\Workshops\BuildWorkshopIndexData;
use Inertia\Inertia;
use Inertia\Response;

class WorkshopIndexController extends Controller
{
    public function __invoke(ListWorkshopsIndexRequest $request, BuildWorkshopIndexData $builder): Response
    {
        $user = $request->user();
        assert($user !== null);

        $data = $builder->handle($user, $request->validated());

        return Inertia::render('admin/workshops/Index', [
            'workshopList' => WorkshopListItemResource::collection($data['workshops'])
                ->resolve($request),
            'filters' => $data['filters'],
            'showWorkshopTable' => $data['showWorkshopTable'],
            'workshopTableColumns' => $data['workshopTableColumns'],
            'employeeFilterFields' => $data['employeeFilterFields'],
        ]);
    }
}
