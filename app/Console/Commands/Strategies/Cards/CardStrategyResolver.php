<?php

namespace App\Console\Commands\Strategies\Cards;

use App\Enums\Games;

class CardStrategyResolver
{

    public function __construct(
        private readonly YgoCardsStrategy $ygoCardsStrategy,
        private readonly RiftboundCardsStrategy $riftboundCardsStrategy
    ) {
    }

    public function resolve(Games $games): CardStrategyInterface {
        return match ($games) {
            Games::YGO => $this->ygoCardsStrategy,
            Games::RIFTBOUND => $this->riftboundCardsStrategy,
            default => throw new \InvalidArgumentException("Unsupported game: {$games->value}"),
        };
    }
}
