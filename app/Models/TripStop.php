<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'stop_order',
        'name',
        'lat',
        'lng',
        'estimated_arrival',
        'actual_arrival',
        'seats_taken',
    ];

    protected $casts = [
        'stop_order' => 'integer',
        'seats_taken' => 'integer',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'estimated_arrival' => 'datetime',
        'actual_arrival' => 'datetime',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
