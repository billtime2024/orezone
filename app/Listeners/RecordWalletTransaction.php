<?php

namespace App\Listeners;

use App\Events\WalletDebited;
use Illuminate\Support\Facades\Log;

class RecordWalletTransaction
{
    public function handle(WalletDebited $event): void
    {
        Log::info('Wallet debited', [
            'user_id' => $event->wallet->user_id,
            'amount' => $event->transaction->amount,
        ]);
    }
}
