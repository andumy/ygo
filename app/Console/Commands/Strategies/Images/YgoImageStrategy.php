<?php

namespace App\Console\Commands\Strategies\Images;

use App\Models\Card;
use function config;
use function current;
use function explode;
use function file_get_contents;

class YgoImageStrategy implements ImageStrategyInterface
{

    public function fetchImage(Card $card): array
    {
        $images = [];
        foreach ($card->variantCards as $variantCard) {
            $images[$variantCard->passcode] = file_get_contents(config('ygo.image_url') . $variantCard->passcode . '.jpg');
        }
        return $images;
    }
}
