<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\PriceRepository;
use App\Repositories\SetRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use function config;
use function file_get_contents;
use function preg_match;

/**
 * @deprecated
 */
class FetchPricesViaParserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:prices-parser';

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
        PriceRepository $priceRepository,
        CardInstanceRepository $cardInstanceRepository,
        SetRepository $setRepository
    )
    {

        $cardRepository->chunk(function (Collection $cards) use(
            $priceRepository,
            $cardInstanceRepository,
            $setRepository,
            $cardRepository
        ) {
//            $now = Carbon::now()->format('Y-m-d');
            /** @var Collection<Card> $cards */
            foreach ($cards as $card) {
//                if($card->last_price_fetch?->format('Y-m-d') == $now){
//                    continue;
//                }
//                $cardRepository->updateLastPriceFetched($card);
                $response = Http::get(config('ygo.price.parser') . $card->name)->body();
                preg_match('/(?<=type="application\/json">)(.*)(?=<\/script)/', $response, $json);
                $json = json_decode($json[0], true);

                try{
                    $responseCardsRaw = $json['props']['pageProps']['initialData']['cards'];
                } catch (\Exception $e) {
                    $this->error("$card->name not found");
                    continue;
                }

                foreach ($responseCardsRaw as $responseCard){
//                    $doesImageExists = Storage::exists('public/' . $card->ygo_id.'.jpg');
//                    if(!$doesImageExists) {
//                        try{
//                            $contents = file_get_contents($responseCard['images']['default_image']);
//                            Storage::put('public/' . $card->ygo_id.'.jpg', $contents);
//                            $this->info("Image saved for $card->ygo_id");
//                        } catch (\Exception $e) {
//                        }
//
//                    }
                    $prices = $responseCard['prices'];
                    $region = null;
                    if(isset($prices['EUROPE']['tcgplayer_prices'][0]['marketPrice'])){
                        $region = 'EUROPE';
                    } elseif(isset($prices['US']['tcgplayer_prices'][0]['marketPrice'])){
                        $region = 'US';
                    } elseif(isset($prices['AUSTRALIA']['tcgplayer_prices'][0]['marketPrice'])){
                        $region = 'AUSTRALIA';
                    }

                    if(!$region){
                        $this->error("No price available for $card->name");
                        continue;
                    }

                    $cardInstance = $cardInstanceRepository->findBySetCodeAndRarity(
                        $responseCard['number'],
                        $responseCard['rarity'],
                    );

                    if(!$cardInstance || $cardInstance->price){
                        $this->error("Price already exists for $card->name with code {$responseCard['number']} and rarity {$responseCard['rarity']}. Skipping.");
                        continue;
//                        if(!isset($responseCard['card_set']['set_name'])){
//                            $this->error("No set defined for $card->name with code {$responseCard['number']} and rarity {$responseCard['rarity']}");
//                            continue;
//                        }
//                        $set = $setRepository->findByName($responseCard['card_set']['set_name']);
//                        if(!$set){
//                            $setId = $this->ask("Set was not found for name {$responseCard['card_set']['set_name']}. Enter the set id");
//                        } else {
//                            $setId = $set->id;
//                        }
//                        $cardInstance = $cardInstanceRepository->create(
//                            [
//                                'card_id' => $card->id,
//                                'set_id' => $setId,
//                                'card_set_code' => $responseCard['number'],
//                                'rarity_verbose' => $responseCard['rarity']
//                            ]
//                        );
//
//                        $this->info("Instance created for $card->name with {$responseCard['number']} code and {$responseCard['rarity']} rarity");
                    }

                    try{
                        $market = $prices[$region]['tcgplayer_prices'][0]['marketPrice'];
                    } catch (\Exception $e) {
                        $this->error("No price available for $card->name with code {$responseCard['number']} and rarity {$responseCard['rarity']}");
                        continue;
                    }


                    $priceRepository->updateOrCreate([
                        'card_instance_id' => $cardInstance->id,
                    ], [
                        'date' => Carbon::now()->format('Y-m-d'),
                        'price' => $market ?? 0,
                    ]);

                    $this->info("Price updated for {$cardInstance->card->name} with $cardInstance->card_set_code");
                }
            }
        });
    }
}
