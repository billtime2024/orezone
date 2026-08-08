<?php

namespace App\Events;

use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingAccepted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Booking $booking;

    public Trip $trip;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->trip = $booking->trip;
    }

    public function broadcastAs(): string
    {
        return 'booking.accepted';
    }

    public function broadcastOn(): Channel
    {
        return new Channel('trip.'.$this->trip->id);
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->booking->id,
            'trip_id' => $this->trip->id,
            'host_name' => $this->booking->host->name,
            'status' => $this->booking->status,
        ];
    }
}
