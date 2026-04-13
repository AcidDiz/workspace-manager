<?php

namespace App\Http\Controllers\Admin\Workshops;

use App\Http\Controllers\Controller;
use App\Http\Resources\Workshop\WorkshopFormResource;
use App\Models\Workshop;
use App\Models\WorkshopCategory;
use Inertia\Inertia;
use Inertia\Response;

class WorkshopEditController extends Controller
{
    public function __invoke(Workshop $workshop): Response
    {
        $categories = WorkshopCategory::query()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('admin/workshops/Edit', [
            'workshop' => WorkshopFormResource::make($workshop)->resolve(request()),
            'categories' => $categories,
        ]);
    }
}
