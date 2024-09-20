<?php

namespace App\Repositories;

use App\Models\CardInstance;
use Illuminate\Support\Collection;

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
    public function findBySetCodeAndRarity(string $code, string $rarity): Collection
    {
        return CardInstance::where('card_set_code', $code)->where('rarity_verbose', $rarity)->get();
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
}
