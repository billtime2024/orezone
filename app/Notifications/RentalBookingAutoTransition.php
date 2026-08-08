<?php

namespace App\Notifications;

use App\Models\RentalBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentalBookingAutoTransition extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public RentalBooking $booking,
        public string $fromStatus,
        public string $toStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $listing = $this->booking->listing;
        $statusLabel = match ($this->toStatus) {
            'active' => 'Check-in',
            'completed' => 'Check-out',
            'expired' => 'Booking Expired',
            default => ucfirst($this->toStatus),
        };

        return (new MailMessage)
            ->subject($statusLabel . ' — ' . $listing->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking status has been updated:')
            ->line('**' . $listing->title . '**')
            ->line('Status: ' . $statusLabel)
            ->line('Check-in: ' . $this->booking->check_in)
            ->line('Check-out: ' . $this->booking->check_out)
            ->action('View Booking', url('/portal/rentals-bookings/' . $this->booking->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'rental_booking_auto_transition',
            'booking_id' => $this->booking->id,
            'listing_title' => $this->booking->listing->title,
            'from_status' => $this->fromStatus,
            'to_status' => $this->toStatus,
        ];
    }
}
