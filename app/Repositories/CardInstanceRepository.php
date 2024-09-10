<?php

namespace App\Repositories;

use App\Models\CardInstance;
use Illuminate\Support\Collection;

class CardInstanceRepository
{
    public function create(array $data): CardInstance{
        return CardInstance::create($data);
    }
    public function firstOrCreate(array $find, array $data): CardInstance
    {
        return CardInstance::firstOrCreate($find, $data);
    }
    public function findBySetCode(string $code): Collection{
        return CardInstance::where('card_set_code', $code)->get();
    }

    public function findMissingRarity(): Collection{
        return CardInstance::where('rarity_code','')->get();
    }

    public function findById(int $id): ?CardInstance{
        return CardInstance::find($id);
    }

    public function rarities(): Collection{
        return CardInstance::select('rarity_verbose','rarity_code')
            ->groupBy('rarity_verbose','rarity_code')
            ->distinct()
            ->get()
            ->pluck('rarity_verbose','rarity_code');
    }
}
