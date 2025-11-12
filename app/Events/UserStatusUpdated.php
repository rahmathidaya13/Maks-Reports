<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserStatusUpdated implements ShouldBroadcast
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
    public function broadcastOn()
    {
        return [
            new Channel('user-status'),
        ];
    }

    /**
     * Nama event yang akan diterima oleh Echo.
     */
    public function broadcastAs()
    {
        return 'status.changed';
    }
}
