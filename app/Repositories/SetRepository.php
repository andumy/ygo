<?php

namespace App\Repositories;

use App\Models\Set;

class SetRepository
{
    public function firstOrCreate(array $find, array $data): Set
    {
        return Set::firstOrCreate($find, $data);
    }
}
