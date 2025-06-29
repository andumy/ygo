<?php

namespace App\Repositories;

use App\Models\Card;
use App\Models\CardInstance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CardRepository
{
    public function firstOrCreate(array $find, array $data): Card
    {
        return Card::firstOrCreate($find, $data);
    }

    public function markHasImage(Card $card): void
    {
        $card->has_image = true;
        $card->save();
    }

    public function create(string $name, ?int $ygoId, string $type): Card
    {
        return Card::create([
            'name' => $name,
            'ygo_id' => $ygoId,
            'type' => $type
        ]);
    }

    /** @return string[] */
    public function types(): array{
        return Card::select('type')
            ->distinct()
            ->get()
            ->pluck('type')
            ->toArray();
    }

    public function findByName(string $name): ?Card
    {
        return Card::where('name', $name)->orWhere('alias', $name)->first();
    }

    public function paginate(
        string $search = '',
        string $set = '',
        int $pagination = 100,
        ?bool $onlyOwned = null,
    )
    {
        return $this->searchQuery(
            search: $search,
            set: $set,
            onlyOwned: $onlyOwned,
        )->paginate($pagination);
    }

    public function getForOrder(int $orderId): Collection
    {
        return Card::whereHas('cardInstances', function ($q) use ($orderId) {
            $q->whereHas('variants', function ($qq) use ($orderId){
                $qq->whereHas('ownedCards', function ($qqq) use ($orderId) {
                    $qqq->where('order_id', $orderId);
                });
            });
        })->get();
    }

    public function chunk(callable $callback): void
    {
        Card::chunk(100, $callback);
    }

    public function findByYgoId(string $id): ?Card
    {
        return Card::whereHas('cardInstances', function ($q) use($id){
            $q->whereHas('variants', function ($qq) use ($id) {
                $qq->whereHas('variantCard', fn ($qqq) => $qqq->where('ygo_id', $id));
            });
        })->first();
    }

    public function count(
        string $search = '',
        string $set = '',
        ?bool $onlyOwned = null,
    ): int {
        return $this->searchQuery(
            search: $search,
            set: $set,
            onlyOwned: $onlyOwned,
        )->count();
    }

    public function countOwnedAndOrderedInsideSet(string $set = ''): int {
        return $this->searchQuery(set: $set)
            ->whereHas('cardInstances', function ($q) use($set) {
                $q->whereHas('set', fn($qq) => $qq->where('name', $set))
                    ->whereHas('variants', fn($qq) => $qq->whereHas('ownedCards'));
            })->count();
    }

    public function searchQuery(
        string $search = '',
        string $set = '',
        ?bool $onlyOwned = null,
    ) {
        return Card::when($search !== '', function($q) use ($search){
                return $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', '%' . $search . '%')
                        ->orWhereHas('cardInstances', function ($qqq) use ($search) {
                            $qqq->where('card_set_code', 'like', '%' . $search . '%')
                                ->orWhereHas('variants', function ($qqqq) use ($search) {
                                    $qqqq->whereHas('variantCard',
                                        fn($qqqqq) => $qqqqq->where('ygo_id', 'like', '%' . $search . '%')
                                    );
                                });
                        });
                });
            })
            ->when($set !== '', function($q) use ($set) {
                $q->whereHas('sets', function ($qq) use ($set) {
                    $qq->where('name', $set);
                });
            })
            ->when($onlyOwned === false, function($q) use ($set) {
                $q->whereDoesntHave('cardInstances', function ($qq) use ($set) {
                    $qq->whereHas('variants', fn($qqq) => $qqq->whereHas('ownedCards'));
                    if($set !== '') {
                        $qq->whereHas('set', function ($qqq) use ($set) {
                            $qqq->where('name', $set);
                        });
                    }
                });

            })
            ->when($onlyOwned, function($q) use ($set) {
                $q->whereHas('cardInstances', function ($qq) use ($set) {
                    $qq->whereHas('variants', fn($qqq) => $qqq->whereHas('ownedCards'));
                    if($set !== '') {
                        $qq->whereHas('set', function ($qqq) use ($set) {
                            $qqq->where('name', $set);
                        });
                    }
                });
            })
            ->whereHas('cardInstances')
            ->when($set !== '', function($q) use ($set) {
                $q->orderBy(
                    CardInstance::select('card_set_code')
                        ->whereColumn('card_instances.card_id', 'cards.id')
                        ->whereHas('set', function ($qq) use ($set) {
                            $qq->where('name', $set);
                        })
                        ->orderBy('card_set_code')
                        ->limit(1)
                );
            });
    }
}
