<?php

namespace App\Models;

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
 * @property string type
 * @property Collection<CardInstance> instances
 * @property Collection<Set> sets
 * @property boolean isOwned
 * @property boolean isOrdered
 */
class Card extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

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
            return $instance->ownedCard?->order_id !== null;
        })->isNotEmpty() && !$this->isOwned;
    }
}
