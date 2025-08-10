<?php

namespace App\Console\Commands;

use App\Enums\Rarities;
use App\Models\Card;
use App\Models\Set;
use App\Models\VariantCard;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\PriceRepository;
use App\Repositories\SetRepository;
use App\Repositories\VariantCardRepository;
use App\Repositories\VariantRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use function array_key_exists;
use function config;

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
        CardRepository         $cardRepository,
        SetRepository          $setRepository,
        CardInstanceRepository $cardInstanceRepository,
        VariantCardRepository  $variantCardRepository,
        VariantRepository      $variantRepository,
        PriceRepository        $priceRepository,
    ): void
    {
        $sets = Http::get(config('ygo.sets'))->json();

        foreach ($sets as $setData) {
            $date = Carbon::parse($setData['tcg_date']);
            $set = $setRepository->findByName($setData['set_name']);

            if (!$set) {
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

        $setRepository->firstOrCreate([
            'name' => 'Setless Cards',
        ], [
            'card_amount' => 0,
            'date' => Carbon::now(),
            'code' => 'SETLESS',
        ]);

        $response = Http::get(config('ygo.cards'));

        $response->lazy('/data')->each(function ($data, $key) use (
            $cardRepository,
            $setRepository,
            $cardInstanceRepository,
            $variantCardRepository,
            $variantRepository,
            $priceRepository,
        ) {
            $this->info("Processing card {$data['name']}.");

            if(!array_key_exists('card_sets', $data)){
                $this->warn("Card {$data['name']} has no instances. Skipped.");
            }

            if (!$variantCardRepository->findById($data['id'], true)) {
                $nonOgVariantCard = $variantCardRepository->findById($data['id'], false);
                if ($nonOgVariantCard) {
                    //check if there is other variant for that card that is Og.
                    $cards = new Collection();
                    foreach ($nonOgVariantCard->variants as $variant) {
                        $cards->push($variant->cardInstance->card);
                    }

                    $cards = $cards->unique('id');

                    if ($cards->count() > 1) {
                        $this->error("Ygo Id {$data['id']} has a variant card non-og associated that goes up to multiple cards");
                        return;
                    }

                    /** @var Card $card */
                    $card = $cards->first();

                    $isThereAnOg = false;
                    foreach ($card->variantCards as $variantCard) {
                        if ($variantCard->is_original) {
                            $isThereAnOg = true;
                            $variantCard->is_original = false;
                            $variantCard->save();
                            $this->info("Variant card {$variantCard->ygo_id} is no longer original.");
                        }
                    }

                    if($isThereAnOg){
                        $this->info("No original variant card found for $card->name.");
                    }

                    $nonOgVariantCard->is_original = true;
                    $nonOgVariantCard->save();

                    $this->info("Variant card {$data['id']} is now original for $card->name.");

                } else {
                    $variantCardRepository->create($data['id'], true);
                    $this->info("Original variant card {$data['id']} was created");
                }
            }

            $allVariantCards = new Collection();
            foreach ($data['card_images'] ?? [] as $variant) {
                $variantCard = $variantCardRepository->findById($variant['id']);
                if (!$variantCard) {
                    $variantCard = $variantCardRepository->create($variant['id'], $variant['id'] === $data['id']);
                    $this->info("Non-Original variant card {$data['id']} was created");
                }
                $allVariantCards->push($variantCard);
            }

            /** @var Card $card */
            $card = $allVariantCards->first()?->cardInstances?->first()?->card;

            if(!$card) {
                $card = $cardRepository->findByName($data['name']);
                if(!$card){
                    $card = $cardRepository->firstOrCreate([
                        'name' => $data['name'],
                        'type' => $data['type'],
                    ],[]);
                }
            }

            if($card->name !== $data['name']){
                $this->alert("Card $card->name renamed to {$data['name']}.");
                $card->name = $data['name'];
                $card->has_image = false;
                $card->save();
            }

            $this->createCardInstances(
                $setRepository,
                $cardInstanceRepository,
                $variantRepository,
                $priceRepository,
                $data,
                $allVariantCards,
                $card
            );
        });
    }

    /**
     * @param SetRepository $setRepository
     * @param CardInstanceRepository $cardInstanceRepository
     * @param VariantRepository $variantRepository
     * @param PriceRepository $priceRepository
     * @param array $data
     * @param Collection<VariantCard> $allVariantCards
     * @param Card $card
     * @return void
     */
    private function createCardInstances(
        SetRepository          $setRepository,
        CardInstanceRepository $cardInstanceRepository,
        VariantRepository      $variantRepository,
        PriceRepository        $priceRepository,
        array                  $data,
        Collection             $allVariantCards,
        Card                   $card
    ): void
    {
        $fallbackSet = [
            "set_name" => 'Setless Cards',
            "set_code" => "",
            "set_rarity" => "",
            "set_rarity_code" => "",
            "set_price" => "0"
        ];
        foreach ($data['card_sets'] ?? [$fallbackSet] as $dataSet) {
            $set = $setRepository->findByName($dataSet['set_name']);
            if (!$set) {
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

            if ($ci->wasRecentlyCreated) {
                $this->info("Card {$card->name} from set {$set->name} added with set code {$ci->card_set_code}.");
            }

            foreach ($allVariantCards as $variantCard){
                $variant = $variantRepository->firstOrCreate([
                    'card_instance_id' => $ci->id,
                    'variant_card_id' => $variantCard->id,
                ],[]);

                if ($variant->wasRecentlyCreated) {
                    $this->info("Variant for {$ci->card_set_code} over ygo id {$variantCard->ygo_id} was added.");
                }
            }

            $priceRepository->updateOrCreate(
                [
                    'card_instance_id' => $ci->id,
                ],
                [
                    'date' => Carbon::now()->format('Y-m-d'),
                    'price' => (float)$dataSet['set_price'] != 0 ?
                        (float)$dataSet['set_price'] : (float)$data['card_prices'][0]['cardmarket_price'],
                ]
            );

        }
    }
}
