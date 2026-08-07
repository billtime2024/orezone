<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TopupRequest;
use App\Http\Resources\Api\V1\WalletResource;
use App\Http\Resources\Api\V1\WalletTransactionResource;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * GET /wallet — Get or create user wallet.
     */
    public function show(Request $request): JsonResponse
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['currency' => 'INR']
        );

        return response()->json([
            'data' => new WalletResource($wallet),
        ]);
    }

    /**
     * GET /wallet/transactions — Paginated wallet transactions.
     */
    public function transactions(Request $request): AnonymousResourceCollection
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['currency' => 'INR']
        );

        $query = $wallet->transactions()->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $transactions = $query->paginate($request->integer('per_page', 20));

        return WalletTransactionResource::collection($transactions);
    }

    /**
     * POST /wallet/topups — Top up wallet balance.
     */
    public function topup(TopupRequest $request): JsonResponse
    {
        $user = $request->user();
        $amount = $request->validated('amount');

        $result = DB::transaction(function () use ($user, $amount) {
            $wallet = Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $user->id],
                ['currency' => 'INR']
            );

            $balanceBefore = $wallet->balance;

            // In a real app, this would call a payment gateway and create a payment_order.
            // Simulate immediate success for now.
            $orderStatus = 'completed';

            // Credit the wallet
            $wallet->increment('balance', $amount);
            $wallet->refresh();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'direction' => WalletTransaction::DIRECTION_CREDIT,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'type' => 'topup',
                'status' => WalletTransaction::STATUS_COMPLETED,
                'metadata' => [
                    'order_status' => $orderStatus,
                ],
            ]);

            return [
                'balance' => $wallet->balance,
                'transaction_id' => $transaction->id,
            ];
        });

        return response()->json([
            'message' => 'Wallet topped up successfully.',
            'balance' => $result['balance'],
            'transaction_id' => $result['transaction_id'],
        ], 201);
    }
}
