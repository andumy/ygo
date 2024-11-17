<?php

namespace App\Repositories;

use App\Models\TradableCard;

class TradableCardRepository
{
    public function updateOrCreate(array $find, array $data): TradableCard{
        return TradableCard::updateOrCreate($find, $data);
    }
}
