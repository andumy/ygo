<?php

namespace App\Repositories;

use App\Models\Card;

class CardRepository
{
    public function firstOrCreate(array $find, array $data): Card
    {
        return Card::firstOrCreate($find, $data);
    }

    public function paginate(string $search, string $set, int $pagination)
    {
        return $this->searchQuery($search , $set)->paginate($pagination);
    }

    public function chunk(callable $callback): void
    {
        Card::chunk(100, $callback);
    }

    public function findByYgoId(string $id): ?Card
    {
        return Card::where('ygo_id', $id)->first();
    }

    public function count(string $search = '', string $set = ''): int {
        return $this->searchQuery($search , $set)->count();
    }

    public function countOwned(string $search = '', string $set = ''): int {
        return $this->searchQuery($search , $set)
            ->whereHas('cardInstances', function ($query) use ($search) {
                $query->whereHas('ownedCard');
            })->count();
    }

    private function searchQuery(string $search = '', string $set = '') {
        if ($search) {
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
                });
        }

        return Card::when($set !== '', function($q) use ($set) {
            return $q->whereHas('sets', function ($query) use ($set) {
                $query->where('name', $set);
            });
        });
    }
}
