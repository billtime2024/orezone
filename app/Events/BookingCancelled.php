<?php

namespace App\Events;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Booking $booking;

    public Trip $trip;

    public User $cancelledBy;

    public function __construct(Booking $booking, User $cancelledBy)
    {
        $this->booking = $booking;
        $this->trip = $booking->trip;
        $this->cancelledBy = $cancelledBy;
    }

    public function broadcastAs(): string
    {
        return 'booking.cancelled';
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
            'cancelled_by' => $this->cancelledBy->name,
        ];
    }
}
