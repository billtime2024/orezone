<?php

namespace App\Notifications;

use App\Models\RentalBooking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentalBookingCreated extends Notification implements ShouldQueue
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
        $guest = $this->booking->guest;

        return (new MailMessage)
            ->subject('New Booking Request — ' . $listing->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have a new booking request for your listing:')
            ->line('**' . $listing->title . '**')
            ->line('Guest: ' . $guest->name)
            ->line('Check-in: ' . $this->booking->check_in)
            ->line('Check-out: ' . $this->booking->check_out)
            ->line('Amount: ₹' . number_format($this->booking->total_amount, 0))
            ->line('Type: ' . ucfirst($this->booking->booking_type) . ' booking')
            ->action('View Booking', url('/portal/rentals-bookings/' . $this->booking->id))
            ->line('Please confirm or reject this booking within 48 hours.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'rental_booking_created',
            'booking_id' => $this->booking->id,
            'listing_title' => $this->booking->listing->title,
            'guest_name' => $this->booking->guest->name,
            'check_in' => $this->booking->check_in,
            'check_out' => $this->booking->check_out,
            'total_amount' => $this->booking->total_amount,
        ];
    }
}
