<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodOrderStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'food_order_status_history';

    protected $fillable = [
        'food_order_id',
        'status',
        'changed_by',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(FoodOrder::class, 'food_order_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by');
    }
}
