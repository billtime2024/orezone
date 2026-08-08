<?php

namespace App\Notifications;

use App\Models\RentalBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentalBookingConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public RentalBooking $booking,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $listing = $this->booking->listing;

        return (new MailMessage)
            ->subject('Booking Confirmed! — ' . $listing->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Great news! Your booking has been confirmed:')
            ->line('**' . $listing->title . '**')
            ->line('Check-in: ' . $this->booking->check_in)
            ->line('Check-out: ' . $this->booking->check_out)
            ->line('Amount: ₹' . number_format($this->booking->total_amount, 0))
            ->action('View Booking', url('/portal/rentals-bookings/' . $this->booking->id))
            ->line('We look forward to your stay!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'rental_booking_confirmed',
            'booking_id' => $this->booking->id,
            'listing_title' => $this->booking->listing->title,
            'check_in' => $this->booking->check_in,
            'check_out' => $this->booking->check_out,
            'total_amount' => $this->booking->total_amount,
        ];
    }
}
