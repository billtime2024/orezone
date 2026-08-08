<?php

namespace App\Enums;

enum RentalBookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Active = 'active';
    case Completed = 'completed';
    case CancelledByGuest = 'cancelled_by_guest';
    case CancelledByHost = 'cancelled_by_host';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Disputed = 'disputed';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [
                'confirmed' => ['host', 'system'],
                'rejected' => ['host'],
                'expired' => ['system'],
                'cancelled_by_guest' => ['guest'],
            ],
            self::Confirmed => [
                'active' => ['system'],
                'cancelled_by_guest' => ['guest'],
                'cancelled_by_host' => ['host'],
            ],
            self::Active => [
                'completed' => ['system'],
                'disputed' => ['guest', 'host', 'admin'],
            ],
            self::Completed => [
                'disputed' => ['guest', 'host'],
            ],
            default => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return array_key_exists($to->value, $this->allowedTransitions());
    }

    public function canActorTransitionTo(self $to, string $actor): bool
    {
        return in_array($actor, $this->allowedTransitions()[$to->value] ?? []);
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Confirmation',
            self::Confirmed => 'Confirmed',
            self::Active => 'Checked In',
            self::Completed => 'Completed',
            self::CancelledByGuest => 'Cancelled by Guest',
            self::CancelledByHost => 'Cancelled by Host',
            self::Rejected => 'Rejected by Host',
            self::Expired => 'Expired',
            self::Disputed => 'Disputed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Confirmed => 'blue',
            self::Active => 'green',
            self::Completed => 'gray',
            self::CancelledByGuest => 'red',
            self::CancelledByHost => 'red',
            self::Rejected => 'red',
            self::Expired => 'gray',
            self::Disputed => 'orange',
        };
    }
}
