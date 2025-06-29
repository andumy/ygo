<?php

namespace App\Repositories;

use App\Enums\Rarities;
use App\Enums\Sale;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VariantRepository
{

    public function find(int $variantId): Variant
    {
        return Variant::find($variantId);
    }

    public function getAllTradableNotCollected(): Collection
    {
        return Variant::whereHas('ownedCards', function ($qq) {
            $qq->whereIn('sale', [Sale::TRADE, Sale::LISTED]);
        })
            ->whereDoesntHave('ownedCards', function ($qq) {
                $qq->where('sale', Sale::IN_COLLECTION);
            })->get();
    }

    /** @return Collection<Variant> */
    public function getBySetCodeAndRarity(string $code, Rarities $rarity, bool $isListed = false): Collection
    {
        return Variant::whereHas('cardInstance',
            fn($q) => $q->where('card_set_code', $code)
                ->where('rarity_verbose', $rarity->value)
        )
            ->when($isListed, function ($q) {
                $q->whereHas('ownedCards', function ($qq) {
                    $qq->where('sale', Sale::LISTED);
                });
            })
            ->get();
    }

    /** @return Collection<Variant> */
    public function getBySetCode(string $code, bool $isListed = false): Collection
    {
        return Variant::whereHas('cardInstance', fn($q) => $q->where('card_set_code', $code))
            ->when($isListed, function ($q) {
                $q->whereHas('ownedCards', function ($qq) {
                    $qq->where('sale', Sale::LISTED);
                });
            })
            ->get();
    }

    /** @return Collection<Variant> */
    public function search(string $search = '', string $set = '', ?bool $onlyOwned = null, bool $excludeOrdered = false): Collection
    {
        return $this->searchQuery(
            search: $search,
            set: $set,
            onlyOwned: $onlyOwned,
            excludeOrdered: $excludeOrdered,
        )->join('card_instances', 'variants.card_instance_id', '=', 'card_instances.id')
            ->select('variants.*')
            ->orderBy('card_instances.card_set_code')
            ->get();
    }

    public function firstOrCreate(array $find, array $data): Variant
    {
        return Variant::firstOrCreate($find, $data);
    }

    private function searchQuery(string $search = '', string $set = '', ?bool $onlyOwned = null, bool $excludeOrdered = false): Builder
    {
        return Variant::when($search !== '', function ($q) use ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->whereHas('cardInstance', function ($qqq) use ($search) {
                    $qqq->where('card_set_code', 'like', '%' . $search . '%')
                        ->orWhereHas('card', fn($qqqq) => $qqqq->where('name', 'like', '%' . $search . '%'));
                })
                    ->orWhereHas('variantCard', function ($qqq) use ($search) {
                        $qqq->where('ygo_id', 'like', '%' . $search . '%');
                    });
            });
        })
            ->when($set !== '', function ($q) use ($set) {
                $q->whereHas('cardInstance', function ($qq) use ($set) {
                    $qq->whereHas('set', function ($qqq) use ($set) {
                        $qqq->where('name', $set);
                    });
                });
            })
            ->when($onlyOwned === false, function ($q) {
                $q->whereDoesntHave('ownedCards');
            })
            ->when($onlyOwned, function ($q) use ($excludeOrdered) {
                if ($excludeOrdered) {
                    $q->whereHas('ownedCards', fn($qq) => $qq->whereNull('order_id'));
                } else {
                    $q->whereHas('ownedCards');
                }
            });
    }

    public function getPurchasableVariants(int $setId): Collection
    {
        // Get rarities in order for comparison
        $rarityCases = Rarities::cases();
        $rarityOrder = array_flip(array_map(fn($r) => $r->value, $rarityCases));

        // Convert rarity order to a CASE statement for MySQL ordering
        $rarityOrderSql = "CASE ci.rarity_verbose ";
        foreach ($rarityOrder as $rarityValue => $position) {
            $rarityOrderSql .= "WHEN ".DB::connection()->getPdo()->quote($rarityValue)." THEN $position ";
        }
        $rarityOrderSql .= "ELSE " . count($rarityOrder) . " END";

        return Variant::query()
            ->select('variants.*')
            ->join('variant_cards as vc', 'variants.variant_card_id', '=', 'vc.id')
            ->join('card_instances as ci', 'variants.card_instance_id', '=', 'ci.id')
            ->leftJoin('owned_cards as oc', 'variants.id', '=', 'oc.variant_id')
            ->whereNull('oc.id') // No owned cards
            ->where('vc.is_original', true) // Only original variant cards
            ->where('ci.set_id', $setId) // Only variants from the specified set
            ->whereNotExists(function($query) {
                // Exclude cards where ANY variant of the same card (regardless of rarity) is owned
                $query->select(DB::raw(1))
                    ->from('variants as v_owned')
                    ->join('card_instances as ci_owned', 'v_owned.card_instance_id', '=', 'ci_owned.id')
                    ->join('owned_cards as oc_owned', 'v_owned.id', '=', 'oc_owned.variant_id')
                    ->whereRaw('ci_owned.card_id = ci.card_id')
                    ->whereRaw('ci_owned.set_id = ci.set_id')
                    ->whereRaw('ci_owned.card_set_code = ci.card_set_code');
            })
            ->whereIn('variants.id', function($query) use ($rarityOrderSql, $setId) {
                $query->selectRaw('MIN(v2.id)')
                    ->from('variants as v2')
                    ->join('card_instances as ci2', 'v2.card_instance_id', '=', 'ci2.id')
                    ->leftJoin('owned_cards as oc2', 'v2.id', '=', 'oc2.variant_id')
                    ->join('variant_cards as vc2', 'v2.variant_card_id', '=', 'vc2.id')
                    ->whereNull('oc2.id')
                    ->where('vc2.is_original', true)
                    ->where('ci2.set_id', $setId)
                    ->groupBy('ci2.card_id', 'ci2.set_id', 'ci2.card_set_code')
                    ->havingRaw("MIN($rarityOrderSql)");
            })
            ->with(['cardInstance.card', 'cardInstance.set', 'variantCard'])
            ->orderBy('ci.card_set_code')
            ->get();
    }
}
