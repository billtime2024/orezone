<?php

namespace App\Listeners;

use App\Events\RentalBookingCancelled;
use App\Events\RentalBookingConfirmed;
use App\Events\RentalBookingCreated;
use App\Events\RentalBookingRejected;
use App\Models\AppNotification;
use App\Models\User;

class SendRentalBookingNotification
{
    public function handle($event): void
    {
        match (true) {
            $event instanceof RentalBookingCreated => $this->handleCreated($event),
            $event instanceof RentalBookingConfirmed => $this->handleConfirmed($event),
            $event instanceof RentalBookingCancelled => $this->handleCancelled($event),
            $event instanceof RentalBookingRejected => $this->handleRejected($event),
        };
    }

    protected function handleCreated(RentalBookingCreated $event): void
    {
        $booking = $event->booking;
        $listing = $booking->listing;

        // Notify owner
        $owner = User::find($booking->owner_id);
        if ($owner) {
            AppNotification::create([
                'user_id' => $owner->id,
                'type' => 'rental_booking_created',
                'title' => 'New Rental Booking',
                'body' => 'A new booking request has been made for your listing "' . $listing->title . '".',
                'data' => [
                    'booking_id' => $booking->id,
                    'listing_id' => $listing->id,
                    'guest_id' => $booking->user_id,
                    'check_in' => $booking->check_in,
                    'check_out' => $booking->check_out,
                ],
            ]);
        }
    }

    protected function handleConfirmed(RentalBookingConfirmed $event): void
    {
        $booking = $event->booking;
        $listing = $booking->listing;

        // Notify guest
        AppNotification::create([
            'user_id' => $booking->user_id,
            'type' => 'rental_booking_confirmed',
            'title' => 'Booking Confirmed',
            'body' => 'Your booking for "' . $listing->title . '" has been confirmed.',
            'data' => [
                'booking_id' => $booking->id,
                'listing_id' => $listing->id,
                'check_in' => $booking->check_in,
                'check_out' => $booking->check_out,
            ],
        ]);
    }

    protected function handleCancelled(RentalBookingCancelled $event): void
    {
        $booking = $event->booking;
        $listing = $booking->listing;
        $cancelledBy = $event->cancelledBy;

        // Notify the OTHER party
        $notifyUserId = ($cancelledBy === 'guest') ? $booking->owner_id : $booking->user_id;

        AppNotification::create([
            'user_id' => $notifyUserId,
            'type' => 'rental_booking_cancelled',
            'title' => 'Booking Cancelled',
            'body' => 'The booking for "' . $listing->title . '" has been cancelled by the ' . $cancelledBy . '.',
            'data' => [
                'booking_id' => $booking->id,
                'listing_id' => $listing->id,
                'cancelled_by' => $cancelledBy,
            ],
        ]);
    }

    protected function handleRejected(RentalBookingRejected $event): void
    {
        $booking = $event->booking;
        $listing = $booking->listing;

        // Notify guest
        AppNotification::create([
            'user_id' => $booking->user_id,
            'type' => 'rental_booking_rejected',
            'title' => 'Booking Rejected',
            'body' => 'Your booking for "' . $listing->title . '" has been rejected.',
            'data' => [
                'booking_id' => $booking->id,
                'listing_id' => $listing->id,
            ],
        ]);
    }
}
