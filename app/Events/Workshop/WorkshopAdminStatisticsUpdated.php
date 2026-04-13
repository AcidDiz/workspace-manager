<?php

namespace App\Events\Workshop;

use App\Broadcasting\PrivateBroadcastEvent;

class WorkshopAdminStatisticsUpdated extends PrivateBroadcastEvent
{
    private const CHANNEL = 'admin.workshop-statistics';

    private const EVENT = 'statistics.updated';

    /**
     * @param  array<string, mixed>  $statistics
     */
    public function __construct(public array $statistics) {}

    protected function broadcastChannelName(): string
    {
        return self::CHANNEL;
    }

    protected function broadcastEventName(): string
    {
        return self::EVENT;
    }

    /**
     * @return array<string, mixed>
     */
    protected function broadcastPayload(): array
    {
        return ['statistics' => $this->statistics];
    }
}
