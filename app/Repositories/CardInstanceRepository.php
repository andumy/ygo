<?php

namespace App\Repositories;

use App\Models\CardInstance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
    public function getBySetCode(string $code): Collection
    {
        return CardInstance::where('card_set_code', $code)->get();
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
    'select sum(p.price) as total from card_instances as ci, owned_cards as oc, prices as p, variants as v where
            ci.id = v.card_instance_id and
            oc.variant_id = v.id and
            ci.id = p.card_instance_id and
            oc.batch is not null'
        );

        return round(current($result)->total, 2);
    }

    public function count(string $search = '', string $set = '', ?bool $onlyOwned = null, bool $excludeOrdered = false): int{
        $sub = $this->searchQuery(
            search: $search,
            set: $set,
            onlyOwned: $onlyOwned,
            excludeOrdered: $excludeOrdered,
        )->groupBy('card_set_code')->select('card_set_code');

        return DB::table( DB::raw("({$sub->toSql()}) as sub") )
            ->mergeBindings($sub->getQuery())
            ->count();

    }

    private function searchQuery(string $search = '', string $set = '', ?bool $onlyOwned = null, bool $excludeOrdered = false): Builder {
        return CardInstance::when($search !== '', function($q) use ($search){
            $q->where(function ($qq) use ($search) {
                $qq->where('card_set_code', 'like', '%' . $search . '%')
                    ->orWhereHas('card', fn ($qqq) => $qqq->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('variants', fn ($qqq) => $qqq->whereHas('variantCard',
                        fn($qqqq) => $qqqq->where('ygo_id', 'like', '%' . $search . '%')
                    ));
            });
        })
            ->when($set !== '', function($q) use ($set) {
                $q->whereHas('set', function ($qq) use ($set) {
                    $qq->where('name', $set);
                });
            })
            ->when($onlyOwned === false, function($q) {
                $q->where(
                    fn($qq) => $qq->whereDoesntHave('variants',
                        fn($qqq) => $qqq->whereHas('ownedCards')
                    )
                );
            })
            ->when($onlyOwned, function($q) use ($excludeOrdered){
                if($excludeOrdered){
                    $q->whereHas('variants', function ($qq) {
                        $qq->whereHas('ownedCards', fn($q) => $q->whereNull('order_id'));
                    });
                } else {
                    $q->whereHas('variants', function ($qq) {
                        $qq->whereHas('ownedCards');
                    });
                }
            });
    }
}
