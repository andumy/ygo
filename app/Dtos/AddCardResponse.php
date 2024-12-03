<?php

namespace App\Dtos;

use App\Enums\AddCardStatuses;
use App\Models\CardInstance;
use Illuminate\Support\Collection;

class AddCardResponse
{
    public function __construct(
        public AddCardStatuses $status,
        public ?Collection $options = null,
    )
    {
    }
}
