<?php

namespace App\Repositories;

use App\Enums\Condition;
use App\Enums\Games;
use App\Enums\Lang;
use App\Enums\Sale;
use App\Models\OwnedCard;
use App\Models\OwnedCardWithAmount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use function collect;

class OwnedCardRepository
{

    public function count(): int
    {
        return OwnedCard::where('sale', '!=', Sale::LISTED)
            ->join('variants', 'owned_cards.variant_id', '=', 'variants.id')
            ->join('card_instances', 'variants.card_instance_id', '=', 'card_instances.id')
            ->where(
                'card_instances.game_id',
                Session::get('game_id') ?? Games::YGO->id()
            )->count();
    }

    public function purgeListed(): void
    {
        OwnedCard::where('sale', Sale::LISTED)->join('variants', 'owned_cards.variant_id', '=', 'variants.id')
            ->join('card_instances', 'variants.card_instance_id', '=', 'card_instances.id')
            ->where(
                'card_instances.game_id',
                Session::get('game_id') ?? Games::YGO->id()
            )->delete();
    }

    public function createAmount(
        int       $variantId,
        int       $batch,
        int       $amount,
        Lang      $lang = Lang::ENGLISH,
        Condition $condition = Condition::NEAR_MINT,
        int       $orderId = null,
        ?bool     $isFirstEdition = null,
        Sale      $sale = Sale::NOT_SET
    ): Collection
    {
        $ownedCards = collect();
        for ($i = 0; $i < $amount; $i++) {
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

    public function sellAmount(
        int       $variantId,
        int       $amount,
        Lang      $lang = Lang::ENGLISH,
        Condition $condition = Condition::NEAR_MINT,
        ?bool     $isFirstEdition = null,
    ): int
    {
        $totalDeleted = 0;
        for ($i = 0; $i < $amount; $i++) {
            $wasUpdated = $this->sell(
                variantId: $variantId,
                lang: $lang,
                condition: $condition,
                isFirstEdition: $isFirstEdition,
            );
            if ($wasUpdated) {
                $totalDeleted++;
            }
        }
        return $totalDeleted;
    }

    public function create(
        int       $variantId,
        int       $batch,
        Lang      $lang = Lang::ENGLISH,
        Condition $condition = Condition::NEAR_MINT,
        int       $orderId = null,
        ?bool     $isFirstEdition = null,
        Sale      $sale = Sale::NOT_SET
    ): OwnedCard
    {
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

    public function sell(
        int       $variantId,
        Lang      $lang = Lang::ENGLISH,
        Condition $condition = Condition::NEAR_MINT,
        ?bool     $isFirstEdition = null,
    ): bool
    {
        /** @var OwnedCard|null $ownedCard */
        $ownedCard = OwnedCard::where([
            'variant_id' => $variantId,
            'lang' => $lang,
            'cond' => $condition,
            'sale' => Sale::LISTED,
            'is_first_edition' => $isFirstEdition === null ? false : $isFirstEdition
        ])->first();

        if (!$ownedCard) {
            return false;
        }

        $ownedCard->delete();
        return true;
    }

    /** @return Collection<OwnedCardWithAmount> */
    public function fetchByVariantGroupByAllOverAmount(int $variantId): Collection
    {
        return OwnedCardWithAmount::where('variant_id', $variantId)
            ->where('sale', '!=', Sale::LISTED)
            ->groupBy('variant_id', 'lang', 'cond', 'sale', 'is_first_edition')
            ->selectRaw('variant_id, lang, cond, sale, is_first_edition, count(*) as amount')
            ->orderBy('amount', 'DESC')
            ->get();
    }

    /** @return Collection<OwnedCardWithAmount> */
    public function fetchByOrderWithAmount(int $orderId): Collection
    {
        return OwnedCardWithAmount::where('order_id', $orderId)
            ->where('sale', '!=', Sale::LISTED)
            ->groupBy('variant_id', 'lang', 'cond', 'is_first_edition')
            ->selectRaw('variant_id, lang, cond, is_first_edition, count(*) as amount')
            ->join('variants', 'variants.id', '=', 'owned_cards.variant_id')
            ->join('card_instances', 'card_instances.id', '=', 'variants.card_instance_id')
            ->orderBy('card_instances.card_set_code', 'DESC')
            ->get();
    }

    public function fetchNextBatch(): int
    {
        return OwnedCard::max('batch') + 1;
    }

    /** @return Collection<OwnedCard> */
    public function cardsForUpdate(
        int        $variantId,
        Lang       $lang = Lang::ENGLISH,
        ?Condition $condition = null,
        ?int       $orderId = null,
        ?bool      $isFirstEdition = null
    ): Collection
    {
        return OwnedCard::where('variant_id', $variantId)
            ->where('sale', '!=', Sale::LISTED)
            ->where('lang', $lang)
            ->when($condition, fn($query) => $query->where('cond', $condition))
            ->when($orderId, fn($query) => $query->where('order_id', $orderId))
            ->when($orderId === null, fn($query) => $query->whereNull('order_id'))
            ->when($isFirstEdition !== null, fn($query) => $query->where('is_first_edition', $isFirstEdition))
            ->orderBy(DB::raw('FIELD(sale, "IN COLLECTION", "TRADE", "NOT SET")'))
            ->get();
    }

    public function shipOrder(int $orderId): void
    {
        OwnedCard::where('order_id', $orderId)->update(['order_id' => null]);
    }

    public function resetSale(
        int       $variantId,
        Lang      $lang,
        Condition $condition,
        bool      $isFirstEdition
    ): void
    {
        OwnedCard::where('variant_id', $variantId)
            ->where('sale', '!=', Sale::LISTED)
            ->where('lang', $lang)
            ->where('cond', $condition)
            ->where('is_first_edition', $isFirstEdition)
            ->delete();
    }

    public function delete(int $ownCardId): void
    {
        OwnedCard::where('id', $ownCardId)->delete();
    }

    /** @return Collection<OwnedCardWithAmount> */
    public function getTradable(): Collection
    {
        return OwnedCardWithAmount::where('sale', Sale::TRADE)
            ->where('order_id', null)
            ->groupBy('variant_id', 'lang', 'cond', 'is_first_edition')
            ->selectRaw('variant_id, lang, cond, is_first_edition, count(*) as amount')
            ->join('variants', 'variants.id', '=', 'owned_cards.variant_id')
            ->join('card_instances', 'card_instances.id', '=', 'variants.card_instance_id')
            ->join('sets', 'sets.id', '=', 'card_instances.set_id')
            ->orderBy('sets.code', 'DESC')
            ->orderBy('card_instances.card_set_code', 'ASC')
            ->limit(100)
            ->get();
    }

    public function markListed(OwnedCard $ownedCard): void
    {
        OwnedCard::where('lang', $ownedCard->lang)
            ->where('cond', $ownedCard->cond)
            ->where('is_first_edition', $ownedCard->is_first_edition)
            ->where('sale', Sale::TRADE)
            ->where('variant_id', $ownedCard->variant_id)
            ->update(['sale' => Sale::LISTED]);
    }
}
