<?php

namespace App\Models;

use App\Enums\Lang;
use App\Enums\Sale;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use function array_key_exists;
use function collect;

/**
 * @property int id
 * @property int card_instance_id
 * @property int variant_card_id
 * @property CardInstance cardInstance
 * @property Collection<OwnedCard> ownedCards
 * @property VariantCard variantCard
 * @property boolean isOwned
 * @property boolean isOrdered
 * @property boolean isMissing
 * @property boolean isCollected
 * @property array orderAmountByLangAndCond
 * @property array ownAmountByLangAndCond
 * @property array orderAmountByLang
 * @property array ownAmountByLang
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

    public function variantCard(): BelongsTo
    {
        return $this->belongsTo(VariantCard::class);
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

    public function getIsCollectedAttribute(): bool
    {
        return $this->ownedCards()->whereNull('order_id')->where('sale', Sale::IN_COLLECTION)->exists();
    }

    public function isOwnedForLang(Lang $lang): bool
    {
        return $this->ownedCards()->whereNull('order_id')->where('lang', $lang)->exists();
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
        return collect($this->ownedCards->whereNull('order_id')->where('sale', '!=', Sale::SOLD)->reduce([$this, 'buildAmountByLang'], []));
    }

    public function getOrderAmountByLangAttribute(): Collection
    {
        return collect($this->ownedCards->whereNotNull('order_id')->where('sale', '!=', Sale::SOLD)->reduce([$this, 'buildAmountByLang'], []));
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
}
