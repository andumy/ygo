<?php

namespace App\Repositories;

use App\Models\CardInstance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use function dd;

class CardInstanceRepository
{
    public function create(array $data): CardInstance
    {
        return CardInstance::create($data);
    }

    public function firstOrCreate(array $find, array $data): CardInstance
    {
        return CardInstance::firstOrCreate($find, $data);
    }

    /** @return Collection<CardInstance> */
    public function findBySetCode(string $code): Collection
    {
        return CardInstance::where('card_set_code', $code)->get();
    }

    /** @return CardInstance */
    public function findBySetCodeAndRarity(string $code, string $rarity): ?CardInstance
    {
        return CardInstance::where('card_set_code', $code)->where('rarity_verbose', $rarity)->first();
    }

    public function findById(int $id): ?CardInstance
    {
        return CardInstance::find($id);
    }

    public function rarities(): Collection
    {
        return CardInstance::select('rarity_verbose')
            ->distinct()
            ->get()
            ->pluck('rarity_verbose');
    }

    public function priceForOwnOrOrder(): float
    {
        $rawPrice = CardInstance::leftJoin('owned_cards', 'card_instances.id', '=', 'owned_cards.card_instance_id')
            ->leftJoin(
                DB::raw('(SELECT card_instance_id, SUM(amount) as total_ordered_amount FROM ordered_cards GROUP BY card_instance_id) as ordered_summary'), 'card_instances.id', '=', 'ordered_summary.card_instance_id')
            ->join('prices', 'card_instances.id', '=', 'prices.card_instance_id')
            ->selectRaw(
                'SUM((COALESCE(owned_cards.amount, 0) + COALESCE(ordered_summary.total_ordered_amount, 0)) * prices.price) as price'
            )
            ->first()
            ->toArray()['price'];

        return round($rawPrice, 2);
    }

    public function count(string $search = '', string $set = '', int $ownedFilter = 0): int{
        return $this->searchQuery($search, $set, $ownedFilter)->count();
    }

    /** @return Collection<CardInstance> */
    public function search(string $search = '', string $set = '', int $ownedFilter = 0): Collection{
        return $this->searchQuery($search, $set, $ownedFilter)->get();
    }


    private function searchQuery(string $search = '', string $set = '', int $ownedFilter = 0) {
        return CardInstance::when($search !== '', function($q) use ($search){
            return $q->where(function ($qq) use ($search) {
                $qq->where('card_set_code', 'like', '%' . $search . '%')
                    ->orWhereHas('card', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('ygo_id', 'like', '%' . $search . '%');
                    });
            });
        })
            ->when($set !== '', function($q) use ($set) {
                return $q->whereHas('set', function ($qq) use ($set) {
                    $qq->where('name', $set);
                });
            })
            ->when($ownedFilter === -1, function($q) {
                return $q->whereDoesntHave('ownedCard')
                    ->orWhereDoesntHave('orderedCards');
            })
            ->when($ownedFilter === 1, function($q) {
                return $q->whereHas('ownedCard')
                    ->orWhereHas('orderedCards');
            });
    }
}
