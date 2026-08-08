<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory;

    const DIRECTION_CREDIT = 'credit';

    const DIRECTION_DEBIT = 'debit';

    const STATUS_COMPLETED = 'completed';

    const STATUS_PENDING = 'pending';

    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'wallet_id',
        'user_id',
        'direction',
        'amount',
        'balance_before',
        'balance_after',
        'type',
        'status',
        'reference_type',
        'reference_id',
        'idempotency_key',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
