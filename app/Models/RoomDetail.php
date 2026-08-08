<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomDetail extends Model
{
    protected $table = 'room_details';

    protected $fillable = [
        'rental_listing_id', 'room_type', 'stay_type',
        'meals_included', 'meal_plan',
        'ac', 'wifi', 'laundry', 'housekeeping', 'curfew_time',
        'check_in_time', 'check_out_time',
        'rules', 'common_areas', 'total_rooms', 'available_rooms',
    ];

    protected $casts = [
        'meals_included' => 'boolean',
        'ac' => 'boolean',
        'wifi' => 'boolean',
        'laundry' => 'boolean',
        'housekeeping' => 'boolean',
        'curfew_time' => 'boolean',
        'total_rooms' => 'integer',
        'available_rooms' => 'integer',
        'rules' => 'array',
        'common_areas' => 'array',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(RentalListing::class, 'rental_listing_id');
    }
}
