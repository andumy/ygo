<?php

namespace App\Repositories;

use App\Models\OwnedCard;

class OwnedCardRepository
{
    public function create(array $data): OwnedCard{
        return OwnedCard::create($data);
    }

    public function countAmountOfCards(): int {
        return OwnedCard::sum('amount');
    }

}
