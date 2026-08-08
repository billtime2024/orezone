<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Auth\Access\HandlesAuthorization;

class WalletPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the wallet.
     */
    public function view(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id;
    }

    /**
     * Determine whether the user can view wallet transactions.
     */
    public function viewTransactions(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id;
    }
}
