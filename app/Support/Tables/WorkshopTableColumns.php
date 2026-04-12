<?php

namespace App\Support\Tables;

use App\Models\User;
use App\Models\WorkshopCategory;
use Illuminate\Support\Collection;

class WorkshopTableColumns
{
    /**
     * Admin index table: column metadata + filter wiring (filter_param is the query string key).
     *
     * @param  Collection<int, WorkshopCategory>  $categories
     * @param  Collection<int, User>  $creators
     * @return list<array<string, mixed>>
     */
    public static function adminTable(Collection $categories, Collection $creators): array
    {
        $categoryOptions = $categories->map(fn($c) => [
            'value' => (string) $c->id,
            'label' => $c->name,
        ])->values()->all();

        $creatorOptions = $creators->map(fn($u) => [
            'value' => (string) $u->id,
            'label' => $u->name,
        ])->values()->all();

        $statusOptions = [
            ['value' => 'all', 'label' => 'Upcoming and closed'],
            ['value' => 'upcoming', 'label' => 'Upcoming'],
            ['value' => 'closed', 'label' => 'Closed'],
        ];

        return [
            [
                'field_name' => 'title',
                'label' => 'Title',
                'placeholder' => 'Filter by title',
                'cast_type' => 'string',
                'input_type' => 'text',
                'filterable' => true,
                'filter_param' => 'title',
                'sortable' => true,
                'default_sort' => 'asc',
            ],
            [
                'field_name' => 'category.name',
                'label' => 'Category',
                'placeholder' => 'Select Category',
                'cast_type' => 'string',
                'input_type' => 'select',
                'filterable' => true,
                'filter_param' => 'category_id',
                'options' => $categoryOptions,
                'sortable' => true,
                'default_sort' => 'asc',
            ],
            [
                'field_name' => 'starts_at',
                'label' => 'Starts at',
                'placeholder' => 'Date',
                'cast_type' => 'datetime',
                'input_type' => 'date',
                'filterable' => true,
                'filter_param' => 'starts_on',
                'sortable' => true,
                'default_sort' => 'asc',
            ],
            [
                'field_name' => 'creator.name',
                'label' => 'Created by',
                'placeholder' => 'Select Author',
                'cast_type' => 'string',
                'input_type' => 'select',
                'filterable' => true,
                'filter_param' => 'created_by',
                'options' => $creatorOptions,
                'sortable' => true,
                'default_sort' => 'asc',
            ],
            [
                'field_name' => 'timing_status',
                'label' => 'Timing',
                'placeholder' => 'Select Status',
                'cast_type' => 'workshop_timing',
                'input_type' => 'select',
                'filterable' => true,
                'filter_param' => 'status',
                'options' => $statusOptions,
                'sortable' => true,
                'default_sort' => 'asc',
            ],
        ];
    }

    /**
     * Employee card toolbar: subset of filters (no created_by).
     *
     * @param  Collection<int, WorkshopCategory>  $categories
     * @return list<array<string, mixed>>
     */
    public static function employeeFilters(Collection $categories): array
    {
        $categoryOptions = $categories->map(fn($c) => [
            'value' => (string) $c->id,
            'label' => $c->name,
        ])->values()->all();

        $statusOptions = [
            ['value' => 'all', 'label' => 'Upcoming and closed'],
            ['value' => 'upcoming', 'label' => 'Upcoming'],
            ['value' => 'closed', 'label' => 'Closed'],
        ];

        return [
            [
                'field_name' => 'category.name',
                'label' => 'Category',
                'placeholder' => 'Select Category',
                'cast_type' => 'string',
                'input_type' => 'select',
                'filterable' => true,
                'filter_param' => 'category_id',
                'options' => $categoryOptions,
                'sortable' => false,
                'default_sort' => null,
            ],
            [
                'field_name' => 'starts_at',
                'label' => 'Starts on',
                'placeholder' => 'Date',
                'cast_type' => 'datetime',
                'input_type' => 'date',
                'filterable' => true,
                'filter_param' => 'starts_on',
                'sortable' => false,
                'default_sort' => null,
            ],
            [
                'field_name' => 'timing_status',
                'label' => 'Timing',
                'placeholder' => 'Select Status',
                'cast_type' => 'workshop_timing',
                'input_type' => 'select',
                'filterable' => true,
                'filter_param' => 'status',
                'options' => $statusOptions,
                'sortable' => false,
                'default_sort' => null,
            ],
        ];
    }
}
