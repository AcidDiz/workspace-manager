<?php

namespace App\Exceptions\Workshop;

use DomainException;

class WorkshopRegistrationException extends DomainException
{
    public static function workshopClosed(): self
    {
        return new self(__('This workshop is no longer open for registration.'));
    }

    public static function alreadyRegistered(): self
    {
        return new self(__('You are already registered for this workshop.'));
    }

    public static function scheduleOverlap(): self
    {
        return new self(__('You already have a registration that overlaps this workshop time.'));
    }
}
