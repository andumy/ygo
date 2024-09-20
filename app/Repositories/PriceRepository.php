<?php

namespace App\Repositories;

use App\Models\Price;
use Carbon\Carbon;

class PriceRepository
{
    public function updateOrCreate(array $find, array $data): Price{
        return Price::updateOrCreate($find, $data);
    }

}
