<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminWalletTransactionController extends Controller
{
    /**
     * List all wallet transactions (admin view).
     */
    public function index(Request $request)
    {
        $query = WalletTransaction::with(['wallet.user:id,name,email', 'user:id,name,email']);

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by direction
        if ($request->filled('direction')) {
            $query->where('direction', $request->input('direction'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->input('to_date') . ' 23:59:59');
        }

        $transactions = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        // Calculate summary
        $summary = [
            'total_credit' => WalletTransaction::where('direction', 'credit')->sum('amount'),
            'total_debit' => WalletTransaction::where('direction', 'debit')->sum('amount'),
            'total_transactions' => WalletTransaction::count(),
        ];

        return Inertia::render('admin/wallets/transactions', [
            'transactions' => $transactions,
            'summary' => $summary,
            'filters' => $request->only(['user_id', 'type', 'direction', 'status', 'from_date', 'to_date']),
        ]);
    }
}
