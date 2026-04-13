<?php

namespace App\Http\Controllers\Admin\Workshops;

use App\Http\Controllers\Controller;
use App\Models\WorkshopCategory;
use Inertia\Inertia;
use Inertia\Response;

class WorkshopCreateController extends Controller
{
    public function __invoke(): Response
    {
        $categories = WorkshopCategory::query()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('admin/workshops/Create', [
            'categories' => $categories,
        ]);
    }
}
