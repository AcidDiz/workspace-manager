<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use Inertia\Inertia;
use Inertia\Response;

class WorkshopController extends Controller
{
    public function index(): Response
    {
        $workshops = Workshop::query()
            ->future()
            ->ordered()
            ->get()
            ->map(fn (Workshop $workshop) => [
                'id' => $workshop->id,
                'title' => $workshop->title,
                'description' => $workshop->description,
                'starts_at' => $workshop->starts_at->toIso8601String(),
                'ends_at' => $workshop->ends_at->toIso8601String(),
                'capacity' => $workshop->capacity,
            ]);

        return Inertia::render('workshops/Index', [
            'upcomingWorkshops' => $workshops,
        ]);
    }
}
