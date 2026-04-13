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

    public static function full(): self
    {
        return new self(__('This workshop is full.'));
    }
}
