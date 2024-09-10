<?php

namespace App\Repositories;

use App\Models\Card;
use Illuminate\Support\Collection;

class CardRepository
{
    public function firstOrCreate(array $find, array $data): Card
    {
        return Card::firstOrCreate($find, $data);
    }

    public function findByName(string $name): ?Card
    {
        return Card::where('name', $name)->first();
    }

    public function paginate(string $search, string $set, bool $hideOwned, int $pagination)
    {
        return $this->searchQuery($search, $set, $hideOwned)->paginate($pagination);
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

    public function count(string $search = '', string $set = '', bool $hideOwned = false): int {
        return $this->searchQuery($search, $set, $hideOwned)->count();
    }

    public function countOwned(string $search = '', string $set = '', bool $hideOwned = false): int {
        return $this->searchQuery($search , $set, $hideOwned)
            ->whereHas('cardInstances', function ($query) use ($search) {
                $query->whereHas('ownedCard');
            })->count();
    }

    private function searchQuery(string $search = '', string $set = '', bool $hideOwned = false) {
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
            ->when($hideOwned, function($q) {
                return $q->whereDoesntHave('cardInstances', function ($qq) {
                    $qq->whereHas('ownedCard');
                });
            });
    }
}
