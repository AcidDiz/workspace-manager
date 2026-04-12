<?php

namespace App\Models;

use App\Enums\WorkshopRegistrationStatus;
use Database\Factories\WorkshopRegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['workshop_id', 'user_id', 'status'])]
class WorkshopRegistration extends Model
{
    /** @use HasFactory<WorkshopRegistrationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => WorkshopRegistrationStatus::class,
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', WorkshopRegistrationStatus::Confirmed);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWaitingList(Builder $query): Builder
    {
        return $query->where('status', WorkshopRegistrationStatus::WaitingList);
    }

    /** @return BelongsTo<Workshop, $this> */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
