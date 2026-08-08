<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WalletResource;
use App\Http\Resources\Api\V1\WalletTransactionResource;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
     *
     * DISABLED: This endpoint previously credited wallet balance without
     * payment gateway verification. It must not be re-enabled until a
     * proper payment integration (Stripe, Razorpay, etc.) is in place.
     */
    public function topup(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Wallet top-up is not implemented yet. This endpoint requires payment gateway integration before it can be enabled.',
        ], 501);
    }
}
