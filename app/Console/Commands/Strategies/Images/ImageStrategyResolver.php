<?php

namespace App\Console\Commands\Strategies\Images;

use App\Console\Commands\Strategies\Cards\CardStrategyInterface;
use App\Console\Commands\Strategies\Cards\RiftboundCardsStrategy;
use App\Console\Commands\Strategies\Cards\YgoCardsStrategy;
use App\Enums\Games;

class ImageStrategyResolver
{

    public function __construct(
        private readonly YgoImageStrategy $ygoImageStrategy,
        private readonly RiftboundImageStrategy $riftboundImageStrategy
    ) {
    }

    public function resolve(Games $games): ImageStrategyInterface {
        return match ($games) {
            Games::YGO => $this->ygoImageStrategy,
            Games::RIFTBOUND => $this->riftboundImageStrategy,
            default => throw new \InvalidArgumentException("Unsupported game: {$games->value}"),
        };
    }
}
