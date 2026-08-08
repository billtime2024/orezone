<?php

namespace App\Listeners;

use App\Events\TripCancelled;
use App\Events\TripPublished;
use App\Models\AppNotification;

class SendTripNotification
{
    public function handle($event): void
    {
        if ($event instanceof TripPublished) {
            $this->handleTripPublished($event);
        } elseif ($event instanceof TripCancelled) {
            $this->handleTripCancelled($event);
        }
    }

    protected function handleTripPublished(TripPublished $event): void
    {
        $trip = $event->trip;

        AppNotification::create([
            'user_id' => $trip->host_id,
            'type' => 'trip_published',
            'title' => 'Trip Published',
            'body' => 'Your trip from '.$trip->origin_name.' to '.$trip->destination_name.' is now live.',
            'data' => [
                'trip_id' => $trip->id,
                'origin' => $trip->origin_name,
                'destination' => $trip->destination_name,
            ],
        ]);
    }

    protected function handleTripCancelled(TripCancelled $event): void
    {
        $trip = $event->trip;

        AppNotification::create([
            'user_id' => $trip->host_id,
            'type' => 'trip_cancelled',
            'title' => 'Trip Cancelled',
            'body' => 'Your trip from '.$trip->origin_name.' to '.$trip->destination_name.' has been cancelled.',
            'data' => [
                'trip_id' => $trip->id,
                'origin' => $trip->origin_name,
                'destination' => $trip->destination_name,
            ],
        ]);
    }
}
