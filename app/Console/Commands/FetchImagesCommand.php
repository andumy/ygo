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

                    if (!$card->ygo_id) {
                        $response = Http::get(
                            config('ygo.cards') . "?name=" . $card->name
                        )->json();

                        if($response['data'] ?? false){
                            $cardRepository->setYgoId($card, current($response['data'])['id']);
                            $this->info("Id fetched for {$card->name}");
                        }

                        if($response['error'] ?? false){
                            $safeName = str_replace(" ", "_", $card->name);
                            $safeName = str_replace("'", "%27", $safeName);
                            $response = Http::get(
                                config('ygo.pedia_url') . $safeName
                            )->body();

                            preg_match('/(?<=cardtable-main_image-wrapper)(.*)(?=div)/', $response, $isolatedData);
                            preg_match('/(?<=srcset=")(.*)(?= 1)/', current($isolatedData), $url);
                            if(count($url) > 0){
                                $url = current($url);
                                $id = Str::uuid();

                                $cardRepository->setYgoId($card, $id->toString());
                                $this->info("Custom id generated for {$card->name}");
                                $contents = file_get_contents($url);
                                Storage::put('public/' . $id->toString() . '.png', $contents);
                                $cardRepository->markHasImage($card);
                                $this->info("Fetched image for {$card->name}");
                                continue;
                            }

                            $this->error("Image could not be parsed for {$card->name}");
                            continue;
                        }
                    }

                    try {
                        if(Storage::exists('public/' . $card->ygo_id . '.jpg')){
                            $cardRepository->markHasImage($card);
                            $this->info("Image already exists for {$card->name}");
                            continue;
                        }

                        $contents = file_get_contents(config('ygo.image_url') . $card->ygo_id . '.jpg');
                        Storage::put('public/' . $card->ygo_id . '.jpg', $contents);
                        $cardRepository->markHasImage($card);
                        $this->info("Fetched image for {$card->name}");
                    } catch (\Exception) {
                    }
                }
            }
        );
    }
}
