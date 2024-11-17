<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property int card_instance_id
 * @property int collectable_amount
 * @property int tradable_amount
 * @property int amount
 * @property CardInstance cardInstance
 */

class TradableCard extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    public function cardInstances(): BelongsTo
    {
        return $this->belongsTo(CardInstance::class);
    }
}
