<?php

namespace App\Repositories;

use App\Models\Card;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CardRepository
{
    public function firstOrCreate(array $find, array $data): Card
    {
        return Card::firstOrCreate($find, $data);
    }

    public function setYgoId(Card $card, string $ygoId): void
    {
        $card->ygo_id = $ygoId;
        $card->save();
    }

    public function markHasImage(Card $card): void
    {
        $card->has_image = true;
        $card->save();
    }

    public function updateLastPriceFetched(Card $card): void{
        $card->last_price_fetch = Carbon::now()->format('Y-m-d');
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

    public function paginate(string $search, string $set, int $pagination, int $ownedFilter = 0)
    {
        return $this->searchQuery($search, $set, $ownedFilter)->paginate($pagination);
    }

    public function getForOrder(int $orderId): Collection
    {
        return Card::whereHas('cardInstances', function ($query) use ($orderId) {
            $query->whereHas('orderedCards', function ($query) use ($orderId) {
                $query->where('order_id', $orderId);
            });
        })->get();
    }

    public function chunk(callable $callback): void
    {
        Card::chunk(100, $callback);
    }

    public function findByYgoId(string $id): ?Card
    {
        return Card::where('ygo_id', $id)->first();
    }

    public function count(string $search = '', string $set = '', int $ownedFilter = 0, bool $includeVariants = true): int {
        return $this->searchQuery($search, $set, $ownedFilter, $includeVariants)->count();
    }

    public function countOwnedAndOrderedInsideSet(string $set = ''): int {
        return $this->searchQuery(set: $set)
            ->whereHas('cardInstances', function ($query) use($set) {
                $query->whereHas('set', fn($q) => $q->where('name', $set))
                    ->where(function($q){
                        $q->whereHas('ownedCard')
                            ->orWhereHas('orderedCards');
                    });
            })->count();
    }

    private function searchQuery(string $search = '', string $set = '', int $ownedFilter = 0, bool $includeVariants = true) {
        return Card::when($search !== '', function($q) use ($search){
            return $q->where(function ($qq) use ($search) {
                $qq
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('ygo_id', 'like', '%' . $search . '%')
                    ->orWhereHas('cardInstances', function ($query) use ($search) {
                        $query->where('card_set_code', 'like', '%' . $search . '%');
                    });
            });
        })
            ->when($set !== '', function($q) use ($set) {
                return $q->whereHas('sets', function ($qq) use ($set) {
                    $qq->where('name', $set);
                });
            })
            ->when($ownedFilter === -1, function($q) {
                return $q->whereDoesntHave('cardInstances', function ($qq) {
                    $qq->whereHas('ownedCard')
                        ->orWhereHas('orderedCards');
                });
            })
            ->when($ownedFilter === 1, function($q) {
                return $q->whereHas('cardInstances', function ($qq) {
                    $qq->whereHas('ownedCard')
                        ->orWhereHas('orderedCards');
                });
            })
            ->when(!$includeVariants, function($q) {
                return $q->whereNull('card_id');
            })
            ->whereHas('cardInstances');
    }
}
