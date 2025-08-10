<?php

namespace App\Console\Commands\Strategies\Cards;

use Illuminate\Http\Client\Response;

interface CardStrategyInterface
{
    public function fetchSets(): array;
    public function fetchCards(): Response;
}
