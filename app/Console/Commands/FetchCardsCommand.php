<?php

namespace App\Console\Commands;

use App\Enums\Rarities;
use App\Models\Card;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\SetRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use function array_key_exists;
use function config;
use function dd;
use function var_dump;

class FetchCardsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:cards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch YGO Cards from API.';

    /**
     * Execute the console command.
     */
    public function handle(
        CardRepository $cardRepository,
        SetRepository $setRepository,
        CardInstanceRepository $cardInstanceRepository
    ): void
    {
        $sets = Http::get(config('ygo.sets'))->json();

        foreach ($sets as $setData) {
            $date = Carbon::parse($setData['tcg_date']);
            $set = $setRepository->findByName($setData['set_name']);

            if(!$set){
                $setRepository->updateOrCreate(
                    [
                        'name' => $setData['set_name'],
                    ],
                    [
                        'code' => $setData['set_code'],
                        'card_amount' => $setData['num_of_cards'],
                        'date' => $date->year > 0 ? $date->format('Y-m-d') : null
                    ]
                );
            }
        }

        $response = Http::get(config('ygo.cards'));

        $response->lazy('/data')->each(function ($data, $key) use ($cardRepository, $setRepository, $cardInstanceRepository) {
            $this->info("Processing card {$data['name']}.");

            $variants = [
                [
                    'id' => $data['id']
                ],
                ...$data['card_images']
            ];

            $ogId = null;
            foreach ($variants as $variant) {
                $isOg = $data['id'] == $variant['id'];
                $card = $cardRepository->firstOrCreate(
                    [
                        'ygo_id' => $variant['id'],
                    ],
                    [
                        'name' => $data['name'],
                        'type' => $data['type'],
                        'card_id' => $isOg ? null : $ogId
                    ]
                );

                if($isOg){
                    $ogId = $card->id;
                    $this->createCardInstances(
                        $setRepository,
                        $cardInstanceRepository,
                        $data,
                        $card
                    );

                } else {
                    $variantResponse = Http::get(config('ygo.cards').'?id='.$variant['id'])->json();
                    $this->createCardInstances(
                        $setRepository,
                        $cardInstanceRepository,
                        $variantResponse['data'][0],
                        $card
                    );
                }

                if($card->wasRecentlyCreated && $card->card_id){
                    $this->info("Card {$card->name} variant was added with id {$card->id}.");
                }
            }
        });
    }

    private function createCardInstances(
        SetRepository $setRepository,
        CardInstanceRepository $cardInstanceRepository,
        array $data,
        Card $card
    ): void{
        foreach ($data['card_sets'] ?? [] as $dataSet){
            $set = $setRepository->findByName($dataSet['set_name']);
            if(!$set){
                $this->error("Set {$dataSet['set_name']} not found.");
                continue;
            }

            $ci = $cardInstanceRepository->firstOrCreate(
                [
                    'card_id' => $card->id,
                    'set_id' => $set->id,
                    'card_set_code' => $dataSet['set_code'],
                    'rarity_verbose' => Rarities::tryFrom($dataSet['set_rarity'])?->value ?? Rarities::MISSING->value,
                ],
                []
            );

            if($ci->wasRecentlyCreated){
                $this->info("Card {$card->name} from set {$set->name} added with set code {$ci->card_set_code}.");
            }
        }
    }
}
