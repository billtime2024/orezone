<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\WalletTransaction;

class WalletTransactionObserver
{
    public function created(WalletTransaction $transaction): void
    {
        AuditLog::create([
            'user_id' => auth()->id() ?? $transaction->user_id,
            'auditable_type' => WalletTransaction::class,
            'auditable_id' => $transaction->id,
            'action' => 'wallet_transaction.created',
            'old_values' => [],
            'new_values' => [
                'amount' => $transaction->amount,
                'type' => $transaction->type,
                'direction' => $transaction->direction,
                'user_id' => $transaction->user_id,
            ],
            'ip_address' => request()?->ip() ?? '127.0.0.1',
            'user_agent' => request()?->userAgent() ?? '',
        ]);
    }
}
