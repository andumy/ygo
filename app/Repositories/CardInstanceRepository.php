<?php

namespace App\Repositories;

use App\Models\CardInstance;
use Illuminate\Support\Collection;

class CardInstanceRepository
{
    public function create(array $data): CardInstance{
        return CardInstance::create($data);
    }
    public function findBySetCode(string $code): Collection{
        return CardInstance::where('card_set_code', $code)->get();
    }

    public function findMissingRarity(): Collection{
        return CardInstance::where('rarity_code','')->get();
    }
}
