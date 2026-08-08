<?php

namespace App\Services\Food;

use App\Models\Food\CateringQuote;
use App\Models\Food\CateringRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CateringService
{
    /**
     * Create a new catering request.
     *
     * @param int   $userId
     * @param array $data  [event_type, guest_count, event_date, event_time,
     *                      venue_address, venue_lat, venue_lng, budget_min,
     *                      budget_max, dietary_requirements, notes]
     * @return \App\Models\CateringRequest
     */
    public function createRequest(int $userId, array $data): CateringRequest
    {
        return CateringRequest::create([
            'request_number'       => $this->generateRequestNumber(),
            'user_id'              => $userId,
            'event_type'           => $data['event_type'],
            'event_name'           => $data['event_name'],
            'guest_count'          => $data['guest_count'],
            'event_date'           => $data['event_date'],
            'event_time'           => $data['event_time'],
            'venue_address'        => $data['venue_address'],
            'venue_latitude'       => $data['venue_latitude'],
            'venue_longitude'      => $data['venue_longitude'],
            'budget_min'           => $data['budget_min'] ?? null,
            'budget_max'           => $data['budget_max'] ?? null,
            'dietary_requirements' => $data['dietary_requirements'] ?? null,
            'notes'                => $data['notes'] ?? null,
            'status'               => CateringRequest::STATUS_PENDING,
        ]);
    }

    /**
     * Generate a unique request number: CAT-YYYYMMDD-XXXXX.
     *
     * @return string
     */
    public function generateRequestNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "CAT-{$date}-";

        $lastRequest = CateringRequest::where('request_number', 'like', "{$prefix}%")
            ->orderByDesc('request_number')
            ->first();

        if ($lastRequest) {
            $lastSequence = (int) substr($lastRequest->request_number, -5);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return $prefix . str_pad((string) $newSequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get all quotes for a catering request.
     *
     * @param int $requestId
     * @return \Illuminate\Support\Collection
     */
    public function getQuotes(int $requestId): Collection
    {
        return CateringQuote::where('catering_request_id', $requestId)
            ->with(['provider:id,name,slug,logo,rating,phone'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Submit a quote for a catering request.
     *
     * @param int   $requestId
     * @param int   $providerId
     * @param array $data  [amount, per_person_rate, menu_description,
     *                      includes, valid_until, notes]
     * @return \App\Models\CateringQuote
     *
     * @throws \InvalidArgumentException
     */
    public function submitQuote(int $requestId, int $providerId, array $data): CateringQuote
    {
        $request = CateringRequest::findOrFail($requestId);

        if ($request->status !== CateringRequest::STATUS_PENDING) {
            throw new InvalidArgumentException('Catering request is no longer open for quotes.');
        }

        // Prevent duplicate quotes from same provider
        $existingQuote = CateringQuote::where('catering_request_id', $requestId)
            ->where('provider_id', $providerId)
            ->first();

        if ($existingQuote) {
            throw new InvalidArgumentException('You have already submitted a quote for this request.');
        }

        return CateringQuote::create([
            'catering_request_id' => $requestId,
            'provider_id'         => $providerId,
            'quoted_amount'       => $data['quoted_amount'],
            'proposed_menu'       => $data['proposed_menu'] ?? null,
            'includes_decor'      => $data['includes_decor'] ?? false,
            'includes_service_staff' => $data['includes_service_staff'] ?? false,
            'valid_until'         => $data['valid_until'] ?? null,
            'notes'               => $data['notes'] ?? null,
            'status'              => 'pending',
        ]);
    }

    /**
     * Accept a catering quote.
     *
     * Updates the request status to 'accepted', marks the chosen quote
     * as accepted, and declines all other quotes.
     *
     * @param int $requestId
     * @param int $quoteId
     * @param int $userId
     * @return \App\Models\CateringRequest
     *
     * @throws \InvalidArgumentException
     */
    public function acceptQuote(int $requestId, int $quoteId, int $userId): CateringRequest
    {
        return DB::transaction(function () use ($requestId, $quoteId, $userId) {
            $request = CateringRequest::lockForUpdate()->findOrFail($requestId);

            if ($request->user_id !== $userId) {
                throw new InvalidArgumentException('You are not authorized to accept quotes for this request.');
            }

            if ($request->status !== CateringRequest::STATUS_PENDING) {
                throw new InvalidArgumentException('Catering request is no longer open.');
            }

            $quote = CateringQuote::where('id', $quoteId)
                ->where('catering_request_id', $requestId)
                ->first();

            if (!$quote) {
                throw new InvalidArgumentException('Quote not found for this request.');
            }

            // Accept the chosen quote
            $quote->update(['status' => 'accepted']);

            // Decline all other quotes
            CateringQuote::where('catering_request_id', $requestId)
                ->where('id', '!=', $quoteId)
                ->update(['status' => 'declined']);

            // Update request
            $request->update([
                'status'              => CateringRequest::STATUS_CONFIRMED,
                'total_amount'        => $quote->quoted_amount,
                'advance_paid'        => $this->calculateAdvancePayment($request, $quote->quoted_amount),
            ]);

            return $request->fresh(['quotes.provider']);
        });
    }

    /**
     * Cancel a catering request.
     *
     * @param int    $requestId
     * @param string $reason
     * @param int    $userId
     * @return \App\Models\CateringRequest
     *
     * @throws \InvalidArgumentException
     */
    public function cancelRequest(int $requestId, string $reason, int $userId): CateringRequest
    {
        return DB::transaction(function () use ($requestId, $reason, $userId) {
            $request = CateringRequest::lockForUpdate()->findOrFail($requestId);

            if ($request->user_id !== $userId) {
                throw new InvalidArgumentException('You are not authorized to cancel this request.');
            }

            if (in_array($request->status, [
                CateringRequest::STATUS_COMPLETED,
                CateringRequest::STATUS_CANCELLED,
            ])) {
                throw new InvalidArgumentException(
                    "Cannot cancel request in status: {$request->status}"
                );
            }

            $request->update([
                'status'         => CateringRequest::STATUS_CANCELLED,
                'cancellation_reason'  => $reason,
                'cancelled_at'   => now(),
            ]);

            // Decline all pending quotes
            CateringQuote::where('catering_request_id', $requestId)
                ->where('status', 'pending')
                ->update(['status' => 'declined']);

            return $request->fresh(['quotes.provider']);
        });
    }

    /**
     * Calculate advance payment (30% of the final amount).
     *
     * @param \App\Models\CateringRequest $request
     * @param float|null $finalAmount  Override (used when accepting a quote)
     * @return float
     */
    public function calculateAdvancePayment(CateringRequest $request, ?float $finalAmount = null): float
    {
        $amount = $finalAmount ?? (float) $request->total_amount;

        return round($amount * 0.30, 2);
    }
}
