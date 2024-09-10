<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property CardInstance cardInstance
 * @property int amount
 * @property int order_amount
 */
class OwnedCard extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    public function cardInstance(): BelongsTo
    {
        return $this->belongsTo(CardInstance::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
