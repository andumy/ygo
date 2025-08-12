<?php

namespace App\Console\Commands;

use App\Console\Commands\Strategies\Images\ImageStrategyResolver;
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
        ImageStrategyResolver $imageStrategyResolver
    ): void
    {
        $cardRepository->chunkWithoutImagesAndGame(
        /** @var Collection<Card> $cards */
            function (Collection $cards) use ($cardRepository, $imageStrategyResolver) {
                foreach ($cards as $card) {
                    $this->info("Processing {$card->name}");
                    if ($card->has_image) {
                        $this->warn("Skipped image for {$card->name}");
                        continue;
                    }

                    $strategy = $imageStrategyResolver->resolve($card->game->name);

                    try {
                        $contents = $strategy->fetchImage($card);
                        foreach ($contents as $passcode => $content) {
                            Storage::put('public/' . $passcode . '.jpg', $content);
                            $this->info("Fetched image for {$card->name} on passcode $passcode");
                        }
                        $cardRepository->markHasImage($card);
                    } catch (\Exception $e) {
                        $this->error("Failed to fetch image for $card->name with passcode $passcode.". $e->getMessage());
                    }
                }
            }
        );
    }
}
