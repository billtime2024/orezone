<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SosAlert extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'active';

    const STATUS_RESPONDED = 'responded';

    const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'user_id',
        'trip_id',
        'latitude',
        'longitude',
        'message',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'reviewed_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
