<?php

namespace App\Http\Controllers\Workshops;

use App\Http\Controllers\Controller;
use App\Http\Resources\Workshop\WorkshopSummaryResource;
use App\Models\Workshop;
use Inertia\Inertia;
use Inertia\Response;

class WorkshopIndexController extends Controller
{
    public function __invoke(): Response
    {
        $workshops = Workshop::query()
            ->future()
            ->ordered()
            ->get();

        return Inertia::render('workshops/Index', [
            'workshopsSummary' => WorkshopSummaryResource::collection($workshops)
                ->resolve(request()),
        ]);
    }
}
