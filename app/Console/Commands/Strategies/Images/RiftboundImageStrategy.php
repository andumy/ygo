<?php

namespace App\Console\Commands\Strategies\Images;

use App\Models\Card;
use function explode;
use function file_get_contents;

class RiftboundImageStrategy implements ImageStrategyInterface
{
    public function fetchImage(Card $card): array
    {
        $images = [];
        foreach ($card->variantCards as $variantCard) {
            $set = current(explode('-', $variantCard->passcode));
            $images[$variantCard->passcode] = file_get_contents("https://cdn.rgpub.io/public/live/map/riftbound/latest/$set/cards/$variantCard->passcode/full-desktop.jpg");
        }
        return $images;
    }
}
