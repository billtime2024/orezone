<?php

namespace App\Events;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletDebited implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WalletTransaction $transaction;

    public Wallet $wallet;

    public function __construct(WalletTransaction $transaction)
    {
        $this->transaction = $transaction;
        $this->wallet = $transaction->wallet;
    }

    public function broadcastAs(): string
    {
        return 'wallet.debited';
    }

    public function broadcastOn(): Channel
    {
        return new Channel('user.'.$this->wallet->user_id);
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'balance_after' => $this->transaction->balance_after,
            'type' => $this->transaction->type,
        ];
    }
}
