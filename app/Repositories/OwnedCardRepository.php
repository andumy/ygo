<?php

namespace App\Repositories;

use App\Enums\Condition;
use App\Enums\Lang;
use App\Enums\Sale;
use App\Models\OwnedCard;
use Illuminate\Support\Collection;

class OwnedCardRepository
{

    public function count(): int {
        return OwnedCard::count();
    }

    public function countTradable(): int {
        return OwnedCard::where('sale', Sale::TRADE)->count();
    }

    public function createAmount(
        int $variantId,
        int $batch,
        int $amount,
        Lang $lang = Lang::ENGLISH,
        Condition $condition = Condition::NEAR_MINT,
        int $orderId = null,
        ?bool $isFirstEdition = null,
        Sale $sale = Sale::NOT_SET
    ): Collection {
        $ownedCards = collect();
        for($i = 0; $i < $amount; $i++){
            $ownedCards->push($this->create(
                variantId: $variantId,
                batch: $batch,
                lang: $lang,
                condition: $condition,
                orderId: $orderId,
                isFirstEdition: $isFirstEdition,
                sale: $sale
            ));
        }
        return $ownedCards;
    }

    public function create(
        int $variantId,
        int $batch,
        Lang $lang = Lang::ENGLISH,
        Condition $condition = Condition::NEAR_MINT,
        int $orderId = null,
        ?bool $isFirstEdition = null,
        Sale $sale = Sale::NOT_SET
    ): OwnedCard {
        return OwnedCard::create([
            'variant_id' => $variantId,
            'lang' => $lang,
            'cond' => $condition,
            'batch' => $batch,
            'order_id' => $orderId,
            'sale' => $sale,
            'is_first_edition' => $isFirstEdition === null ? false : $isFirstEdition
        ]);
    }
    /** @return Collection<OwnedCard> */
    public function fetchByVariantGroupByAllOverAmount(int $variantId): Collection {
        return OwnedCard::where('variant_id', $variantId)
            ->groupBy('variant_id', 'lang', 'cond', 'sale', 'is_first_edition')
            ->selectRaw('variant_id, lang, cond, sale, is_first_edition, count(*) as amount')
            ->orderBy('amount','DESC')
            ->get();
    }

    /** @return Collection<OwnedCard> */
    public function fetchByOrderWithAmount(int $orderId): Collection {
        return OwnedCard::where('order_id', $orderId)
            ->groupBy('variant_id','lang', 'cond', 'is_first_edition')
            ->selectRaw('variant_id, lang, cond, is_first_edition, count(*) as amount')
            ->join('variants', 'variants.id', '=', 'owned_cards.variant_id')
            ->join('card_instances', 'card_instances.id', '=', 'variants.card_instance_id')
            ->orderBy('card_instances.card_set_code','DESC')
            ->get();
    }

    public function atLeastOneCollected(string $code): bool
    {
        return OwnedCard::whereHas('variant', function($q) use ($code){
            $q->whereHas('cardInstance', function($qq) use ($code){
                $qq->where('card_set_code', $code);
            });
        })
            ->where('sale', Sale::IN_COLLECTION)
            ->exists();
    }

    public function fetchNextBatch(): int
    {
        return OwnedCard::max('batch') + 1;
    }

    /** @return Collection<OwnedCard> */
    public function cardsForUpdate(
        int $variantId,
        Lang $lang = Lang::ENGLISH,
        ?Condition $condition = null,
        ?int $orderId = null,
        ?bool $isFirstEdition = null
    ): Collection{
        return OwnedCard::where('variant_id', $variantId)
            ->where('lang', $lang)
            ->when($condition, fn($query) => $query->where('cond', $condition))
            ->when($orderId, fn($query) => $query->where('order_id', $orderId))
            ->when($orderId === null, fn($query) => $query->whereNull('order_id'))
            ->when($isFirstEdition !== null, fn($query) => $query->where('is_first_edition', $isFirstEdition))
            ->orderBy('sale')
            ->orderBy('batch','DESC')
            ->get();
    }

    public function shipOrder(int $orderId): void
    {
        OwnedCard::where('order_id',$orderId)->update(['order_id' => null]);
    }

    public function resetSale(
        int $variantId,
        Lang $lang,
        Condition $condition,
        bool $isFirstEdition
    ): void
    {
        OwnedCard::where('variant_id', $variantId)
            ->where('lang', $lang)
            ->where('cond', $condition)
            ->where('is_first_edition', $isFirstEdition)
            ->delete();
    }

    public function delete(int $ownCardId): void
    {
        OwnedCard::where('id',$ownCardId)->delete();
    }
}
