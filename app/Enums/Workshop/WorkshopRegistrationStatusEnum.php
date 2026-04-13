<?php

namespace App\Enums\Workshop;

enum WorkshopRegistrationStatusEnum: string
{
    case Confirmed = 'confirmed';
    case WaitingList = 'waiting_list';

    public function adminLabel(): string
    {
        return match ($this) {
            self::Confirmed => 'Confirmed',
            self::WaitingList => 'Waiting list',
        };
    }

    /**
     * Tailwind classes for status badges (scanned via `@source` in `resources/css/app.css`).
     */
    public function badgeClassName(): string
    {
        return match ($this) {
            self::Confirmed => 'border-transparent bg-emerald-500/15 text-emerald-700 dark:text-emerald-400',
            self::WaitingList => 'border-transparent bg-amber-500/15 text-amber-950 dark:text-amber-100',
        };
    }
}
