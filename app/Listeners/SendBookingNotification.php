<?php

namespace App\Listeners;

use App\Events\BookingAccepted;
use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Models\AppNotification;

class SendBookingNotification
{
    public function handle($event): void
    {
        if ($event instanceof BookingCreated) {
            $this->handleBookingCreated($event);
        } elseif ($event instanceof BookingAccepted) {
            $this->handleBookingAccepted($event);
        } elseif ($event instanceof BookingConfirmed) {
            $this->handleBookingConfirmed($event);
        } elseif ($event instanceof BookingCancelled) {
            $this->handleBookingCancelled($event);
        }
    }

    protected function handleBookingCreated(BookingCreated $event): void
    {
        $booking = $event->booking;
        $trip = $event->trip;

        AppNotification::create([
            'user_id' => $booking->host_id,
            'type' => 'booking_created',
            'title' => 'New Booking Request',
            'body' => $booking->traveler->name.' has requested '.$booking->seat_count.' seat(s) on your trip from '.$trip->origin_name.' to '.$trip->destination_name.'.',
            'data' => [
                'booking_id' => $booking->id,
                'trip_id' => $trip->id,
                'traveler_id' => $booking->traveler_id,
                'seat_count' => $booking->seat_count,
            ],
        ]);
    }

    protected function handleBookingAccepted(BookingAccepted $event): void
    {
        $booking = $event->booking;

        AppNotification::create([
            'user_id' => $booking->traveler_id,
            'type' => 'booking_accepted',
            'title' => 'Booking Accepted',
            'body' => $booking->host->name.' has accepted your booking request.',
            'data' => [
                'booking_id' => $booking->id,
                'trip_id' => $event->trip->id,
                'host_id' => $booking->host_id,
            ],
        ]);
    }

    protected function handleBookingConfirmed(BookingConfirmed $event): void
    {
        $booking = $event->booking;
        $trip = $event->trip;

        // Notify traveler
        AppNotification::create([
            'user_id' => $booking->traveler_id,
            'type' => 'booking_confirmed',
            'title' => 'Booking Confirmed',
            'body' => 'Your booking on the trip from '.$trip->origin_name.' to '.$trip->destination_name.' has been confirmed. Platform fee: '.$booking->total_platform_fee.'.',
            'data' => [
                'booking_id' => $booking->id,
                'trip_id' => $trip->id,
                'total_platform_fee' => $booking->total_platform_fee,
            ],
        ]);

        // Notify host
        AppNotification::create([
            'user_id' => $booking->host_id,
            'type' => 'booking_confirmed',
            'title' => 'Booking Confirmed',
            'body' => 'The booking by '.$booking->traveler->name.' on your trip from '.$trip->origin_name.' to '.$trip->destination_name.' has been confirmed. Platform fee: '.$booking->total_platform_fee.'.',
            'data' => [
                'booking_id' => $booking->id,
                'trip_id' => $trip->id,
                'total_platform_fee' => $booking->total_platform_fee,
            ],
        ]);
    }

    protected function handleBookingCancelled(BookingCancelled $event): void
    {
        $booking = $event->booking;
        $trip = $event->trip;

        // Notify BOTH host and traveler about the cancellation
        AppNotification::create([
            'user_id' => $booking->host_id,
            'type' => 'booking_cancelled',
            'title' => 'Booking Cancelled',
            'body' => 'A booking on trip '.$trip->origin_name.' → '.$trip->destination_name.' has been cancelled.',
            'data' => [
                'booking_id' => $booking->id,
                'trip_id' => $trip->id,
            ],
        ]);

        AppNotification::create([
            'user_id' => $booking->traveler_id,
            'type' => 'booking_cancelled',
            'title' => 'Booking Cancelled',
            'body' => 'A booking on trip '.$trip->origin_name.' → '.$trip->destination_name.' has been cancelled.',
            'data' => [
                'booking_id' => $booking->id,
                'trip_id' => $trip->id,
            ],
        ]);
    }
}
