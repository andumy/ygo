<?php

namespace App\Models;

use App\Enums\OrderCardStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @property int id
 * @property CardInstance cardInstance
 * @property int amount
 * @property OrderCardStatuses $status
 */
class OrderedCard extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    public $casts = [
        'status' => OrderCardStatuses::class
    ];

    public function cardInstance(): BelongsTo
    {
        return $this->belongsTo(CardInstance::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
