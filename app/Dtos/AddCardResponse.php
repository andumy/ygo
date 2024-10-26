<?php

namespace App\Dtos;

use App\Enums\AddCardStatuses;
use App\Models\CardInstance;

class AddCardResponse
{
    public function __construct(
        public AddCardStatuses $status,
        public array $rarities = [],
        public string $cardName = '',
        public ?CardInstance $cardInstance = null
    )
    {
    }
}
