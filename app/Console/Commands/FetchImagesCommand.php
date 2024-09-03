<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Repositories\CardRepository;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use function config;
use function sleep;
use function usleep;

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
            function (Collection $cards) {
                foreach ($cards as $card) {
                    try{
                        $contents = file_get_contents(config('ygo.image_url') . $card->ygo_id . '.jpg');
                        Storage::put('public/' . $card->ygo_id.'.jpg', $contents);
                        usleep(1000);
                    } catch (\Exception){}
                }
            }
        );
    }
}
