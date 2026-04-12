<?php

namespace App\Support\Filters\Workshops;

use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopCategory;
use App\Support\Tables\WorkshopTableColumns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BuildWorkshopIndexData
{
    /**
     * @param  array<string, mixed>  $validated
     * @return array{
     *   workshops: Collection<int, Workshop>,
     *   filters: array<string, mixed>,
     *   showWorkshopTable: bool,
     *   workshopTableColumns: list<array<string, mixed>>,
     *   employeeFilterFields: list<array<string, mixed>>
     * }
     */
    public function handle(User $user, array $validated): array
    {
        $requestedStatus = $validated['status'] ?? null;
        $effectiveStatus = $requestedStatus ?? ($user->can('workshops.manage') ? 'all' : 'upcoming');

        $query = Workshop::query()
            ->with(['category', 'creator']);

        match ($effectiveStatus) {
            'upcoming' => $query->future(),
            'closed' => $query->past(),
            default => null,
        };

        if (! empty($validated['category_id'])) {
            $query->where('workshop_category_id', $validated['category_id']);
        }

        if (! empty($validated['title'])) {
            $query->where('title', 'like', '%'.$validated['title'].'%');
        }

        if (! empty($validated['starts_on'])) {
            $query->whereDate('starts_at', $validated['starts_on']);
        }

        $showWorkshopTable = $user->can('workshops.manage');

        if ($showWorkshopTable && ! empty($validated['created_by'])) {
            $query->where('created_by', $validated['created_by']);
        }

        $requestedSort = $showWorkshopTable ? ($validated['sort'] ?? null) : null;
        $requestedDirection = $showWorkshopTable ? ($validated['direction'] ?? null) : null;
        $direction = $requestedDirection === 'desc' ? 'desc' : 'asc';

        if ($requestedSort) {
            $query->reorder();
            $this->applyAdminSort($query, (string) $requestedSort, $direction);
        } else {
            $query->indexOrder();
        }

        $workshops = $query->get();

        $categories = WorkshopCategory::query()->orderBy('name')->get();

        $creators = collect();
        if ($showWorkshopTable) {
            $creatorIds = Workshop::query()->distinct()->pluck('created_by')->filter();
            $creators = User::query()
                ->whereIn('id', $creatorIds)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        $filters = [
            'status' => $requestedStatus,
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'] ?? null,
            'starts_on' => $validated['starts_on'] ?? null,
            'created_by' => $showWorkshopTable ? ($validated['created_by'] ?? null) : null,
            'sort' => $showWorkshopTable ? $requestedSort : null,
            'direction' => $showWorkshopTable ? ($requestedSort ? $direction : null) : null,
        ];

        return [
            'workshops' => $workshops,
            'filters' => $filters,
            'showWorkshopTable' => $showWorkshopTable,
            'workshopTableColumns' => $showWorkshopTable
                ? WorkshopTableColumns::adminTable($categories, $creators)
                : [],
            'employeeFilterFields' => $showWorkshopTable
                ? []
                : WorkshopTableColumns::employeeFilters($categories),
        ];
    }

    /**
     * Apply admin table sorting. Sorting is ignored unless requested explicitly.
     *
     * @param  Builder<Workshop>  $query
     */
    private function applyAdminSort(Builder $query, string $sort, string $direction): void
    {
        match ($sort) {
            'title' => $query->orderBy('workshops.title', $direction),
            'starts_at' => $query->orderBy('workshops.starts_at', $direction),
            'category.name' => $query
                ->leftJoin('workshop_categories as wc', 'wc.id', '=', 'workshops.workshop_category_id')
                ->select('workshops.*')
                ->orderBy('wc.name', $direction),
            'creator.name' => $query
                ->leftJoin('users as u', 'u.id', '=', 'workshops.created_by')
                ->select('workshops.*')
                ->orderBy('u.name', $direction),
            'timing_status' => $direction === 'asc'
                ? $query->orderByRaw('CASE WHEN workshops.starts_at > ? THEN 0 ELSE 1 END', [now()])
                    ->orderBy('workshops.starts_at', 'asc')
                : $query->orderByRaw('CASE WHEN workshops.starts_at > ? THEN 1 ELSE 0 END', [now()])
                    ->orderBy('workshops.starts_at', 'desc'),
            default => $query->indexOrder(),
        };
    }
}
