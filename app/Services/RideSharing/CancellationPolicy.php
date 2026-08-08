<?php

namespace App\Services\RideSharing;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Configurable cancellation policy engine.
 *
 * Reads rules from admin_settings table and applies fee/refund/penalty
 * outcomes based on who is cancelling and the booking status.
 *
 * Settings groups:
 *   - cancellation.traveler_before_accept_refund (100 = full refund)
 *   - cancellation.traveler_after_confirm_refund (50 = 50% refund)
 *   - cancellation.host_cancel_penalty (0 = no penalty)
 *   - cancellation.no_show_fee_retain (100 = keep all fees)
 */
class CancellationPolicy
{
    /**
     * Determine the cancellation outcome for a booking.
     *
     * Returns an array with:
     *   - refund_percentage: % of platform fee to refund to traveler
     *   - penalty_percentage: % penalty charged to cancelling party
     *   - description: human-readable explanation
     */
    public function determineOutcome(Booking $booking, User $canceller): array
    {
        $isTraveler = $canceller->id === $booking->traveler_id;
        $isHost = $canceller->id === $booking->host_id;

        if (!$isTraveler && !$isHost) {
            throw new \InvalidArgumentException('Only the traveler or host can cancel a booking.');
        }

        // No-show handling
        if ($booking->status === Booking::STATUS_NO_SHOW) {
            return $this->getNoShowOutcome();
        }

        // Host cancelling
        if ($isHost) {
            return $this->getHostCancelOutcome($booking);
        }

        // Traveler cancelling — outcome depends on booking status
        return $this->getTravelerCancelOutcome($booking);
    }

    /**
     * Get the refund amount based on the outcome.
     *
     * @param array $outcome from determineOutcome()
     * @return float refund amount in INR
     */
    public function calculateRefundAmount(Booking $booking, array $outcome): float
    {
        if ($booking->total_platform_fee <= 0) {
            return 0.0;
        }

        $refundPercentage = $outcome['refund_percentage'] ?? 0;

        return round($booking->total_platform_fee * ($refundPercentage / 100), 2);
    }

    /**
     * Get the penalty amount based on the outcome.
     *
     * @param array $outcome from determineOutcome()
     * @return float penalty amount in INR
     */
    public function calculatePenaltyAmount(Booking $booking, array $outcome): float
    {
        if ($booking->total_platform_fee <= 0) {
            return 0.0;
        }

        $penaltyPercentage = $outcome['penalty_percentage'] ?? 0;

        return round($booking->total_platform_fee * ($penaltyPercentage / 100), 2);
    }

    /**
     * Traveler cancelling before host acceptance (requested status).
     * Full refund — no platform fee was collected yet.
     */
    private function getTravelerCancelOutcome(Booking $booking): array
    {
        if ($booking->status === Booking::STATUS_REQUESTED) {
            $refundPct = $this->getSetting('traveler_before_accept_refund', 100);

            return [
                'refund_percentage' => (float) $refundPct,
                'penalty_percentage' => 0,
                'description' => "Traveler cancelled before host acceptance. {$refundPct}% of platform fee refunded.",
            ];
        }

        // Traveler cancelling after confirmation
        if (in_array($booking->status, [Booking::STATUS_ACCEPTED, Booking::STATUS_CONFIRMED])) {
            $refundPct = $this->getSetting('traveler_after_confirm_refund', 50);

            return [
                'refund_percentage' => (float) $refundPct,
                'penalty_percentage' => 0,
                'description' => "Traveler cancelled after confirmation. {$refundPct}% of platform fee refunded.",
            ];
        }

        return [
            'refund_percentage' => 0,
            'penalty_percentage' => 0,
            'description' => 'Booking cancelled.',
        ];
    }

    /**
     * Host cancelling a booking.
     * Host gets penalized, traveler gets full refund.
     */
    private function getHostCancelOutcome(Booking $booking): array
    {
        $penaltyPct = $this->getSetting('host_cancel_penalty', 0);

        return [
            'refund_percentage' => 100,
            'penalty_percentage' => (float) $penaltyPct,
            'description' => "Host cancelled booking. Traveler receives full refund. {$penaltyPct}% penalty applied to host.",
        ];
    }

    /**
     * No-show outcome — platform keeps fees.
     */
    private function getNoShowOutcome(): array
    {
        $retainPct = $this->getSetting('no_show_fee_retain', 100);

        return [
            'refund_percentage' => 0,
            'penalty_percentage' => 0,
            'description' => "No-show recorded. {$retainPct}% of platform fee retained.",
        ];
    }

    /**
     * Read a cancellation setting from admin_settings.
     */
    private function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = DB::table('admin_settings')
            ->where('group', 'cancellation')
            ->where('key', $key)
            ->first();

        return $setting ? $setting->value : $default;
    }
}
