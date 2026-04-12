<?php

namespace App\Http\Resources\Workshop;

use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Workshop
 */
class WorkshopListItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Workshop $workshop */
        $workshop = $this->resource;

        $isFuture = $workshop->starts_at->isFuture();

        return [
            'id' => $workshop->id,
            'title' => $workshop->title,
            'description' => $workshop->description,
            'starts_at' => $workshop->starts_at->toIso8601String(),
            'ends_at' => $workshop->ends_at->toIso8601String(),
            'capacity' => $workshop->capacity,
            'category' => $workshop->relationLoaded('category') && $workshop->category
                ? [
                    'id' => $workshop->category->id,
                    'name' => $workshop->category->name,
                ]
                : [
                    'id' => null,
                    'name' => '—',
                ],
            'creator' => $workshop->relationLoaded('creator') && $workshop->creator
                ? [
                    'id' => $workshop->creator->id,
                    'name' => $workshop->creator->name,
                ]
                : [
                    'id' => null,
                    'name' => '—',
                ],
            'timing_status' => $isFuture ? 'upcoming' : 'closed',
        ];
    }
}
