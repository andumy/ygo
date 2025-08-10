<?php

namespace App\Console\Commands\Strategies\Images;

use App\Models\Card;
use function explode;
use function file_get_contents;

class RiftboundImageStrategy implements ImageStrategyInterface
{
    public function fetchImage(Card $card): string
    {
        $set = current(explode('-', $card->passcode));
        return file_get_contents("https://cdn.rgpub.io/public/live/map/riftbound/latest/$set/cards/$card->passcode/full-desktop.jpg");
    }
}
