<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $clientUserId;
    public $reservationId;
    public $status; // 'accepte', 'refuse', 'annule', etc.
    public $message;
    public $intervenantName;
    public $serviceName;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($clientUserId, $reservationId, $status, $message, $intervenantName = null, $serviceName = null)
    {
        $this->clientUserId = $clientUserId;
        $this->reservationId = $reservationId;
        $this->status = $status;
        $this->message = $message;
        $this->intervenantName = $intervenantName;
        $this->serviceName = $serviceName;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('client.' . $this->clientUserId);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'reservationId' => $this->reservationId,
            'status' => $this->status,
            'message' => $this->message,
            'intervenantName' => $this->intervenantName,
            'serviceName' => $this->serviceName,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'reservation.status.updated';
    }
}
