<?php

namespace Database\Factories;

use App\Models\Wallet;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'user_id' => User::factory(),
            'direction' => WalletTransaction::DIRECTION_CREDIT,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'balance_before' => 0,
            'balance_after' => 0,
            'type' => 'admin_adjustment',
            'status' => WalletTransaction::STATUS_COMPLETED,
            'idempotency_key' => Str::uuid()->toString(),
        ];
    }
}
