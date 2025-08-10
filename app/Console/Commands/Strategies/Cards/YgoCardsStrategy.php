<?php

namespace App\Console\Commands\Strategies\Cards;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use function config;

class YgoCardsStrategy implements CardStrategyInterface
{

    public function fetchSets(): array
    {
        return Http::get(config('ygo.sets'))->json();
    }

    public function fetchCards(): Response
    {
        return Http::get(config('ygo.cards'));
    }
}
