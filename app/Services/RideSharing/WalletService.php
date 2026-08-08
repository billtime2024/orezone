<?php

namespace App\Services\RideSharing;

use App\Models\Booking;
use App\Events\WalletDebited;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WalletService
{
    /**
     * Get or create a wallet for a user with INR currency.
     */
    public function getOrCreateWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'currency' => 'INR',
                'balance' => 0,
                'is_active' => true,
            ]
        );
    }

    /**
     * Deduct platform fee from a wallet for a booking.
     *
     * Locks the wallet row, validates sufficient balance,
     * creates an immutable ledger entry, and updates the balance.
     * Enforces idempotency via idempotency_key on the booking.
     */
    public function deductPlatformFee(Wallet $wallet, Booking $booking): WalletTransaction
    {
        $amount = (float) $booking->total_platform_fee;

        if ($amount <= 0) {
            throw new InvalidArgumentException('Platform fee must be greater than zero.');
        }

        $idempotencyKey = 'platform_fee_' . $booking->id;

        return DB::transaction(function () use ($wallet, $booking, $amount, $idempotencyKey) {
            // Lock the wallet row to prevent concurrent modifications
            $lockedWallet = Wallet::lockForUpdate()->findOrFail($wallet->id);

            if (!$lockedWallet->is_active) {
                throw new InvalidArgumentException('Wallet is not active.');
            }

            // Idempotency check
            $existing = WalletTransaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            if ($lockedWallet->balance < $amount) {
                throw new InvalidArgumentException(
                    "Insufficient wallet balance. Required: {$amount}, Available: {$lockedWallet->balance}"
                );
            }

            $balanceBefore = (float) $lockedWallet->balance;
            $balanceAfter = $balanceBefore - $amount;

            // Create immutable ledger entry
            $transaction = WalletTransaction::create([
                'wallet_id' => $lockedWallet->id,
                'user_id' => $lockedWallet->user_id,
                'direction' => WalletTransaction::DIRECTION_DEBIT,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'type' => 'platform_fee',
                'status' => WalletTransaction::STATUS_COMPLETED,
                'reference_type' => Booking::class,
                'reference_id' => $booking->id,
                'idempotency_key' => $idempotencyKey,
            ]);

            // Update wallet balance
            $lockedWallet->update(['balance' => $balanceAfter]);

            return $transaction;
        });
    }

    /**
     * Add credit to a wallet.
     *
     * Supports refunds, admin adjustments, and promotional credits.
     * Locks the wallet row, creates an immutable ledger entry,
     * and updates the balance.
     */
    public function addCredit(
        Wallet $wallet,
        float $amount,
        string $type,
        ?Model $reference = null
    ): WalletTransaction {
        $allowedTypes = ['refund', 'admin_adjustment', 'promotional_credit'];

        if (!in_array($type, $allowedTypes)) {
            throw new InvalidArgumentException(
                "Invalid credit type. Allowed: " . implode(', ', $allowedTypes)
            );
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException('Credit amount must be greater than zero.');
        }

        $idempotencyKey = $type . '_' . ($reference ? $reference->getKey() : uniqid());

        return DB::transaction(function () use ($wallet, $amount, $type, $reference, $idempotencyKey) {
            // Lock the wallet row
            $lockedWallet = Wallet::lockForUpdate()->findOrFail($wallet->id);

            if (!$lockedWallet->is_active) {
                throw new InvalidArgumentException('Wallet is not active.');
            }

            // Idempotency check
            $existing = WalletTransaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            $balanceBefore = (float) $lockedWallet->balance;
            $balanceAfter = $balanceBefore + $amount;

            // Create immutable ledger entry
            $transaction = WalletTransaction::create([
                'wallet_id' => $lockedWallet->id,
                'user_id' => $lockedWallet->user_id,
                'direction' => WalletTransaction::DIRECTION_CREDIT,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'type' => $type,
                'status' => WalletTransaction::STATUS_COMPLETED,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->getKey(),
                'idempotency_key' => $idempotencyKey,
            ]);

            // Update wallet balance
            $lockedWallet->update(['balance' => $balanceAfter]);

            return $transaction;
        });
    }

    /**
     * Get paginated transactions for a wallet.
     *
     * Supports type filter for transaction type.
     */
    public function getTransactions(Wallet $wallet, array $filters = []): LengthAwarePaginator
    {
        $query = WalletTransaction::where('wallet_id', $wallet->id)
            ->orderByDesc('created_at');

        // Type filter
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Direction filter
        if (!empty($filters['direction'])) {
            $query->where('direction', $filters['direction']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }
}
