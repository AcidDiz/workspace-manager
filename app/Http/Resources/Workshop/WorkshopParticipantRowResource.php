<?php

namespace App\Http\Resources\Workshop;

use App\Models\WorkshopRegistration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WorkshopRegistration
 */
class WorkshopParticipantRowResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var WorkshopRegistration $registration */
        $registration = $this->resource;
        $user = $registration->user;
        $status = $registration->status;

        return [
            'id' => $registration->id,
            'user_id' => $registration->user_id,
            'user' => [
                'name' => $user !== null ? $user->name : '—',
                'email' => $user !== null ? $user->email : '—',
            ],
            'registration_status' => $status->value,
            'registration_status_label' => $status->adminLabel(),
            'registration_status_badge_class' => $status->badgeClassName(),
            'created_at' => $registration->created_at->toIso8601String(),
        ];
    }
}
