<?php

namespace App\Repositories;

use App\Models\Card;

class CardRepository
{
    public function firstOrCreate(array $find, array $data): Card
    {
        return Card::firstOrCreate($find, $data);
    }

    public function paginate(string $search, int $pagination)
    {
        if ($search) {
            return Card::where('name', 'like', '%' . $search . '%')
                ->orWhere('ygo_id', 'like', '%' . $search . '%')
                ->orWhereHas('cardInstances', function ($query) use ($search) {
                    $query->where('card_set_code', 'like', '%' . $search . '%');
                })
                ->paginate($pagination);
        }
        return Card::paginate($pagination);
    }

    public function chunk(callable $callback): void
    {
        Card::chunk(100, $callback);
    }
}
