<?php

namespace App\Http\Controllers\Admin\Workshops;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workshops\StoreWorkshopRequest;
use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class WorkshopStoreController extends Controller
{
    public function __invoke(StoreWorkshopRequest $request): RedirectResponse
    {
        Workshop::query()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Workshop created.'),
        ]);

        return redirect()->route('admin.workshops.index');
    }
}
