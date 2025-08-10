<?php

namespace App\Console\Commands\Strategies\Images;

use App\Models\Card;

interface ImageStrategyInterface
{
    public function fetchImage(Card $card): string;
}
