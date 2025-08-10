<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Repositories\CardRepository;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function config;
use function count;
use function current;
use function file_get_contents;
use function preg_match;
use function str_replace;
use function urlencode;

class FetchImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(
        CardRepository $cardRepository,
    ): void
    {
        $cardRepository->chunk(
        /** @var Collection<Card> $cards */
            function (Collection $cards) use ($cardRepository) {
                foreach ($cards as $card) {
                    if ($card->has_image) {
                        continue;
                    }

                    try {
                        $contents = file_get_contents(config('ygo.image_url') . $card->ygoId . '.jpg');
                        Storage::put('public/' . $card->ygoId . '.jpg', $contents);
                        $cardRepository->markHasImage($card);
                        $this->info("Fetched image for {$card->name}");
                    } catch (\Exception) {
                    }
                }
            }
        );
    }
}
