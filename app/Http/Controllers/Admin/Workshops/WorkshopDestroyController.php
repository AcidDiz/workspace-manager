<?php

namespace App\Http\Controllers\Admin\Workshops;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class WorkshopDestroyController extends Controller
{
    public function __invoke(Workshop $workshop): RedirectResponse
    {
        $workshop->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Workshop deleted.'),
        ]);

        return redirect()->route('admin.workshops.index');
    }
}
