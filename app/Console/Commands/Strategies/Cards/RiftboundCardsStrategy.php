<?php

namespace App\Console\Commands\Strategies\Cards;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class RiftboundCardsStrategy implements CardStrategyInterface
{

    public function fetchSets(): array
    {
        return Storage::json('datasets/riftbound/sets.json');
    }

    public function fetchCards(): Response
    {
        return new Response(
            new GuzzleResponse(
                200,
                ['Content-Type' => 'application/json'],
                Storage::get('datasets/riftbound/cards.json')
            )
        );
    }
}
