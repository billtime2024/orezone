<?php

namespace App\Notifications;

use App\Models\RentalBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentalBookingCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public RentalBooking $booking,
        public string $cancelledBy, // 'guest' or 'host'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $listing = $this->booking->listing;
        $cancelledByName = $this->cancelledBy === 'guest' ? 'the guest' : 'the host';

        return (new MailMessage)
            ->subject('Booking Cancelled — ' . $listing->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A booking has been cancelled by ' . $cancelledByName . ':')
            ->line('**' . $listing->title . '**')
            ->line('Check-in: ' . $this->booking->check_in)
            ->line('Check-out: ' . $this->booking->check_out)
            ->line('Reason: ' . ($this->booking->cancellation_reason ?? 'No reason provided'))
            ->action('View Details', url('/portal/rentals-bookings/' . $this->booking->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'rental_booking_cancelled',
            'booking_id' => $this->booking->id,
            'listing_title' => $this->booking->listing->title,
            'cancelled_by' => $this->cancelledBy,
            'reason' => $this->booking->cancellation_reason,
        ];
    }
}
