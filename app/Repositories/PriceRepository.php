<?php

namespace App\Repositories;

use App\Models\Price;
use Carbon\Carbon;

class PriceRepository
{
    public function firstOrCreate(array $find, array $data): Price{
        return Price::firstOrCreate($find, $data);
    }

}
