<?php

namespace App\Http\Controllers\Admin\Workshops;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workshops\UpdateWorkshopRequest;
use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class WorkshopUpdateController extends Controller
{
    public function __invoke(UpdateWorkshopRequest $request, Workshop $workshop): RedirectResponse
    {
        $workshop->update($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Workshop updated.'),
        ]);

        return redirect()->route('admin.workshops.index');
    }
}
