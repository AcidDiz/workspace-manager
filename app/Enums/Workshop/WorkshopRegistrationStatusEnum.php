<?php

namespace App\Enums\Workshop;

enum WorkshopRegistrationStatusEnum: string
{
    case Confirmed = 'confirmed';
    case WaitingList = 'waiting_list';
}
