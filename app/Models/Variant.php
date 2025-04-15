<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property int ygo_id
 * @property int card_instance_id
 * @property CardInstance cardInstance
 *
 */
class Variant extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    public function cardInstance(): BelongsTo
    {
        return $this->belongsTo(CardInstance::class);
    }

    public function ownedCards(): HasMany
    {
        return $this->hasMany(OwnedCard::class);
    }
}
