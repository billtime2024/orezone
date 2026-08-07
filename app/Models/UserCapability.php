<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCapability extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_capabilities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'capability',
        'status',
        'granted_at',
        'revoked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Get the user that owns the capability.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
