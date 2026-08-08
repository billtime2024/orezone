<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WalletResource;
use App\Http\Resources\Api\V1\WalletTransactionResource;
use App\Services\RideSharing\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService,
    ) {}

    /**
     * GET /wallet — Get or create user wallet.
     */
    public function show(Request $request): JsonResponse
    {
        // Wallet is created/retrieved for the authenticated user only — ownership is implicit
        $wallet = $this->walletService->getOrCreateWallet($request->user());

        return response()->json([
            'data' => new WalletResource($wallet),
        ]);
    }

    /**
     * GET /wallet/transactions — Paginated wallet transactions.
     */
    public function transactions(Request $request): AnonymousResourceCollection
    {
        $wallet = $this->walletService->getOrCreateWallet($request->user());

        $filters = array_filter([
            'type' => $request->input('type'),
            'per_page' => $request->integer('per_page', 20),
        ], fn ($v) => $v !== null && $v !== '');

        $transactions = $this->walletService->getTransactions($wallet, $filters);

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
