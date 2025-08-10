<?php

namespace App\Console\Commands\Strategies\Images;

use App\Models\Card;
use function config;
use function file_get_contents;

class YgoImageStrategy implements ImageStrategyInterface
{

    public function fetchImage(Card $card): string
    {
        return file_get_contents(config('ygo.image_url') . $card->passcode . '.jpg');
    }
}
