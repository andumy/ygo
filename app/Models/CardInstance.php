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
 * @property Set set
 * @property string card_set_code
 * @property string rarity_verbose
 * @property string rarity_code
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

    public function getRarityAttribute(): string
    {
        if($this->rarity_code){
            return $this->rarity_code;
        }

        return '(' . collect(explode(" ",$this->rarity_verbose))->reduce(function($c, $word){
            $c .= strtoupper($word[0]);
            return $c;
        },'') . ')';
    }
}
