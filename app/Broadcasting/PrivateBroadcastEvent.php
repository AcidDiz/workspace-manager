<?php

namespace App\Broadcasting;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

abstract class PrivateBroadcastEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel($this->broadcastChannelName())];
    }

    public function broadcastAs(): string
    {
        return $this->broadcastEventName();
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return $this->broadcastPayload();
    }

    abstract protected function broadcastChannelName(): string;

    abstract protected function broadcastEventName(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function broadcastPayload(): array;
}
