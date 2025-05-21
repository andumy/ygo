<?php

namespace App\Repositories;

use App\Enums\Rarities;
use App\Enums\Sale;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
}
