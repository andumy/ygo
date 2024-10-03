<?php

namespace App\Console\Commands;

use App\Models\CardInstance;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\PriceRepository;
use App\Repositories\SetRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use function array_search;
use function config;
use function dd;
use function str_contains;

class FetchPricesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Prices';

    /**
     * Execute the console command.
     */
    public function handle(
        CardRepository         $cardRepository,
        SetRepository          $setRepository,
        CardInstanceRepository $cardInstanceRepository,
        PriceRepository        $priceRepository
    )
    {
        $types = $cardRepository->types();

        $sets = Http::get(config('ygo.price.sets'))->json();
        foreach ($sets as $setName) {
            $set = $setRepository->firstOrCreate(
                ['name' => $setName],
                []
            );
            if ($set->wasRecentlyCreated) {
                $this->info("$setName set was created");
            }

            $response = Http::get(config('ygo.price.cards_in_set') . $setName)->json();

            foreach ($response['data']['cards'] ?? [] as $cardObject) {
                $card = $cardRepository->findByName($cardObject['name']);

                if (!$card) {
                    if (str_contains($cardObject['name'],'Token')) {
                        $card = $cardRepository->create($cardObject['name'], null, 'Token');
                    } elseif (
                        $this->choice(
                            "Does the card {$cardObject['name']} already exists?",
                            ["Yes", "No"]
                        ) === "Yes"
                    ) {
                        $name = $this->ask("Enter actual name for {$cardObject['name']}");
                        $card = $cardRepository->findByName($name);
                        $card->alias = $cardObject['name'];
                        $card->save();
                    } else {
                        $ygoId = $this->ask("Enter ygo id for {$cardObject['name']}")??null;
                        $type = $this->choice("Select type for {$cardObject['name']}", $types);
                        $card = $cardRepository->create($cardObject['name'], $ygoId, $type);
                    }
                }

                foreach ($cardObject['numbers'] ?? [] as $instance) {
                    $createNew = false;

                    $cardInstances = $cardInstanceRepository->findBySetCode($instance['print_tag']);
                    if ($cardInstances->isEmpty()) {
                        $createNew = true;
                    }
                    if ($cardInstances->count() === 1) {
                        $cardInstance = $cardInstances->first();
                    }
                    if ($cardInstances->count() > 1) {
                        $filteredCardInstances = $cardInstances->filter(
                            function (CardInstance $ci) use ($instance, $card, $set) {
                                return $ci->card_id === $card->id &&
                                    $ci->set_id === $set->id &&
                                    $ci->rarity_verbose === $instance['rarity'];
                            }
                        );
                        if($filteredCardInstances->count() == 1){
                            $cardInstance = $filteredCardInstances->first();
                        } else {
                            $createNew = true;
                        }
                    }

                    if ($createNew) {
                        $cardInstance = $cardInstanceRepository->firstOrCreate([
                            'card_id' => $card->id,
                            'set_id' => $set->id,
                            'card_set_code' => $instance['print_tag'],
                            'rarity_verbose' => $instance['rarity']
                        ], []);
                    }

                    if ($instance['price_data']['status'] === 'fail') {
                        continue;
                    }

                    $priceObject = $instance['price_data']['data']['prices'];

                    $price = $priceRepository->updateOrCreate([
                        'card_instance_id' => $cardInstance->id,
                    ], [
                        'date' => Carbon::parse($priceObject['updated_at'])->format('Y-m-d'),
                        'low' => $priceObject['low'] ?? 0,
                        'high' => $priceObject['high'] ?? 0,
                        'avg' => $priceObject['average'] ?? 0
                    ]);

                    if($price->wasRecentlyCreated){
                        $this->info("Price for $card->name in $set->name was created");
                    } else {
                        $this->info("Price for $card->name in $set->name was updated");
                    }
                }
            }
        }
    }
}
