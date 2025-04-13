<?php

namespace App\Repositories;

use App\Enums\Rarities;
use App\Models\CardInstance;
use Illuminate\Database\Eloquent\Builder;
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

    /** @return Collection<CardInstance> */
    public function findBySetCodeAndRarity(string $code, Rarities $rarity): Collection
    {
        return CardInstance::where('card_set_code', $code)
            ->where('rarity_verbose', $rarity->value)
            ->get();
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
        $result = DB::select(
    'select sum(p.price) as total from card_instances as ci, owned_cards as oc, prices as p where
            ci.id = oc.card_instance_id and
            ci.id = p.card_instance_id and
            oc.batch is not null'
        );

        return round(current($result)->total, 2);
    }

    public function count(string $search = '', string $set = '', ?bool $onlyOwned = null, bool $excludeOrdered = false, bool $includeVariants = true): int{
        $sub = $this->searchQuery(
            search: $search,
            set: $set,
            onlyOwned: $onlyOwned,
            excludeOrdered: $excludeOrdered,
            includeVariants: $includeVariants
        )->groupBy('card_set_code')->select('card_set_code');

        return DB::table( DB::raw("({$sub->toSql()}) as sub") )
            ->mergeBindings($sub->getQuery())
            ->count();

    }

    /** @return Collection<CardInstance> */
    public function search(string $search = '', string $set = '', ?bool $onlyOwned = null, bool $excludeOrdered = false, bool $includeVariants = true): Collection{
        return $this->searchQuery(
            search: $search,
            set: $set,
            onlyOwned: $onlyOwned,
            excludeOrdered: $excludeOrdered,
            includeVariants: $includeVariants
        )->orderBy('card_set_code')->get();
    }


    private function searchQuery(string $search = '', string $set = '', ?bool $onlyOwned = null, bool $excludeOrdered = false, bool $includeVariants = true): Builder {
        return CardInstance::when($search !== '', function($q) use ($search, $includeVariants){
            return $q->where(function ($qq) use ($search, $includeVariants) {
                $qq->where('card_set_code', 'like', '%' . $search . '%')
                    ->orWhereHas('card', function ($query) use ($search, $includeVariants) {
                        $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('ygo_id', 'like', '%' . $search . '%');
                        if(!$includeVariants){
                            $query->whereNull('card_id');
                        }
                    });
            });
        })
            ->when($set !== '', function($q) use ($set) {
                return $q->whereHas('set', function ($qq) use ($set) {
                    $qq->where('name', $set);
                });
            })
            ->when($onlyOwned === false, function($q) {
                return $q->where(fn($qq) => $qq->whereDoesntHave('ownedCards'));
            })
            ->when($onlyOwned, function($q) use ($excludeOrdered){
                if($excludeOrdered){
                    return $q->whereHas('ownedCards', fn($q) => $q->whereNull('order_id'));
                } else {
                    return $q->whereHas('ownedCards');
                }
            });
    }
}
