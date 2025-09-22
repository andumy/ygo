<?php

namespace App\Models;

use App\Enums\Games;
use App\Enums\Lang;
use App\Enums\Rarities;
use App\Enums\Sale;
use App\Repositories\GameRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use function array_key_exists;
use function collect;
use function current;
use function explode;

/**
 * @property int id
 * @property string name
 * @property Card card
 * @property int card_id
 * @property Set set
 * @property int set_id
 * @property Collection<OwnedCard> ownedCards
 * @property Collection<Variant> variants
 * @property int game_id
 * @property Game game
 * @property Price price
 * @property string card_set_code
 * @property string card_set_code_base
 * @property string card_set_code_nr
 * @property Rarities rarity_verbose
 * @property ?string shortRarity
 * @property array orderAmountByLangAndCond
 * @property array ownAmountByLangAndCond
 * @property array orderAmountByLang
 * @property array ownAmountByLang
 * @property boolean isOwned
 * @property boolean isOrdered
 * @property boolean isMissing
 */

class CardInstance extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'rarity_verbose' => Rarities::class,
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function ownedCards(): HasManyThrough
    {
        return $this->hasManyThrough(OwnedCard::class, Variant::class);
    }

    public function price(): HasOne
    {
        return $this->hasOne(Price::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function getShortRarityAttribute(): string
    {
        return Rarities::tryFrom($this->rarity_verbose->value)->getShortHand() ?? $this->rarity_verbose->value;
    }

    public function buildAmountByLang(array $carry, OwnedCard $ownedCard): array {
        if(!array_key_exists($ownedCard->lang->value, $carry)){
            $carry[$ownedCard->lang->value] = 1;
            return $carry;
        }
        $carry[$ownedCard->lang->value]++;
        return $carry;
    }

    public function getOwnAmountByLangAttribute(): Collection
    {
        return collect($this->ownedCards->whereNull('order_id')->where('sale', '!=', Sale::LISTED)->reduce([$this, 'buildAmountByLang'], []));
    }

    public function getOrderAmountByLangAttribute(): Collection
    {
        return collect($this->ownedCards->whereNotNull('order_id')->where('sale', '!=', Sale::LISTED)->reduce([$this, 'buildAmountByLang'], []));
    }

    public function buildAmountByLangAndCond(array $carry, OwnedCard $ownedCard): array {
        if(!array_key_exists($ownedCard->lang->value, $carry)){
            $carry[$ownedCard->lang->value] = [];
        }

        if(!array_key_exists($ownedCard->cond->value, $carry[$ownedCard->lang->value])){
            $carry[$ownedCard->lang->value][$ownedCard->cond->value] = 1;
            return $carry;
        }

        $carry[$ownedCard->lang->value][$ownedCard->cond->value]++;
        return $carry;
    }

    public function getOwnAmountByLangAndCondAttribute(): Collection
    {
        return collect(
            $this->ownedCards()->whereNull('order_id')->reduce([$this, 'buildAmountByLangAndCond'], [])
        );
    }

    public function getOrderAmountByLangAndCondAttribute(): Collection
    {
        return collect(
            $this->ownedCards->whereNotNull('order_id')->reduce([$this, 'buildAmountByLangAndCond'], [])
        );
    }

    public function getIsOwnedAttribute(): bool
    {
        return $this->ownedCards()->whereNull('order_id')->exists();
    }

    public function getIsOrderedAttribute(): bool
    {
        return $this->ownedCards()->whereNotNull('order_id')->exists() > 0 && !$this->isOwned;
    }

    public function getIsMissingAttribute(): bool
    {
        return !$this->isOwned && !$this->isOrdered;
    }

    public function getCardSetCodeBaseAttribute(): string
    {
        return current(explode('-',$this->card_set_code));
    }
    public function getCardSetCodeNrAttribute(): string
    {
        return substr($this->card_set_code, -3);
    }

    public function isOwnedForLang(Lang $lang): bool
    {
        return $this->ownedCards()->whereNull('order_id')->where('lang', $lang)->exists() > 0;
    }

    protected static function booted(): void
    {
        static::addGlobalScope('game', function (Builder $builder) {
            $builder->where(
                'card_instances.game_id',
        Session::get('game_id') ?? Games::YGO->id()
            );
        });
    }

}
