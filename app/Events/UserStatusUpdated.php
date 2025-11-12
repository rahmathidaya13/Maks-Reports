<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $statusData;
    /**
     * Create a new event instance.
     */
    public function __construct(array $statusData)
    {
        $this->statusData = $statusData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */

    /**
     * Dapatkan channel yang harus disiarkan oleh event.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('user-status');
    }

    /**
     * Nama event yang akan diterima oleh Echo.
     */
    public function broadcastAs(): string
    {
        return 'status.changed';
    }

    public function broadcastWith()
    {
        return $this->statusData;
    }
}
