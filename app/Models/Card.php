<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property int ygo_id
 * @property string name
 * @property string alias
 * @property string type
 * @property Collection<CardInstance> cardInstances
 * @property Collection<Set> sets
 * @property boolean isOwned
 * @property boolean isOrdered
 * @property Carbon last_price_fetch
 */
class Card extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    public $casts = ['last_price_fetch' => 'date'];

    public function cardInstances(): HasMany
    {
        return $this->hasMany(CardInstance::class);
    }

    public function sets(): BelongsToMany
    {
        return $this->belongsToMany(
            Set::class,
            'card_instances',
            'card_id',
            'set_id'
        );
    }

    public function getIsOwnedAttribute(): bool
    {
        return $this->cardInstances->filter(function (CardInstance $instance) {
            return $instance->ownedCard?->amount > 0;
        })->isNotEmpty();
    }
    public function getIsOrderedAttribute(): bool
    {
        return $this->cardInstances->filter(function (CardInstance $instance) {
            return $instance->orderedCards->count() > 0;
        })->isNotEmpty() && !$this->isOwned;
    }
}
