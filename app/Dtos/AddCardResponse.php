<?php

namespace App\Dtos;

use App\Enums\AddCardStatuses;

class AddCardResponse
{
    public function __construct(
        public AddCardStatuses $status,
        public array $rarities = [],
        public string $cardName = ''
    )
    {
    }
}
