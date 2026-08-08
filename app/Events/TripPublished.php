<?php

namespace App\Events;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TripPublished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Trip $trip;

    public User $host;

    public function __construct(Trip $trip)
    {
        $this->trip = $trip;
        $this->host = $trip->host;
    }

    public function broadcastAs(): string
    {
        return 'trip.published';
    }

    public function broadcastOn(): Channel
    {
        return new Channel('trip.'.$this->trip->id);
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->trip->id,
            'origin' => $this->trip->origin_name,
            'destination' => $this->trip->destination_name,
            'departure_at' => $this->trip->departure_at?->toISOString(),
            'available_seats' => $this->trip->available_seats,
        ];
    }
}
