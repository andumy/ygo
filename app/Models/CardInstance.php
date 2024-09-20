<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use function collect;
use function explode;
use function strtoupper;

/**
 * @property int id
 * @property string name
 * @property Card card
 * @property int card_id
 * @property Set set
 * @property int set_id
 * @property OwnedCard ownedCard
 * @property Collection<OrderedCard> orderedCards
 * @property Price price
 * @property string card_set_code
 * @property string rarity_verbose
 * @property string rarity
 */
class CardInstance extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class);
    }

    public function ownedCard(): HasOne
    {
        return $this->hasOne(OwnedCard::class);
    }

    public function orderedCards(): HasMany
    {
        return $this->hasMany(OrderedCard::class);
    }

    public function price(): HasOne
    {
        return $this->hasOne(Price::class);
    }

    public function getRarityAttribute(): string
    {
        return '(' . collect(explode(" ",$this->rarity_verbose))->reduce(function($c, $word){
            $c .= strtoupper($word[0]);
            return $c;
        },'') . ')';
    }
}
