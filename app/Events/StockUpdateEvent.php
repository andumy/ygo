<?php

namespace App\Events;

use App\Models\Set;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockUpdateEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Set $set,
        public readonly bool $stockState,
    )
    {
    }
}
