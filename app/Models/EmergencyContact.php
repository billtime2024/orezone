<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'relation',
    ];

    // ── Relationships ────────────────────────────────────────────────

    /**
     * Get the user who owns this emergency contact.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
