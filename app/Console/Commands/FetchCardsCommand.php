<?php

namespace App\Console\Commands;

use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\SetRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use function config;
use function dd;

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
        $response = Http::get(config('ygo.sets'))->json();

        foreach ($response as $data) {
            $date = Carbon::parse($data['tcg_date']);
            $setRepository->updateOrCreate(
                [
                    'name' => $data['set_name'],
                ],
                [
                    'code' => $data['set_code'],
                    'card_amount' => $data['num_of_cards'],
                    'date' => $date->year > 0 ? $date->format('Y-m-d') : null
                ]
            );
        }

        $response = Http::get(config('ygo.cards'))->json();

        foreach ($response['data'] as $data) {
            $card = $cardRepository->firstOrCreate(
                [
                    'ygo_id' => $data['id']
                ],
                [
                    'name' => $data['name'],
                    'type' => $data['type']
                ]
            );

            foreach ($data['card_sets'] ?? [] as $dataSet){
                $set = $setRepository->firstOrCreate(
                    [
                        'name' => $dataSet['set_name'],
                        'code' => $dataSet['set_code']
                    ],
                    []
                );

                $cardInstanceRepository->firstOrCreate(
                    [
                        'card_id' => $card->id,
                        'set_id' => $set->id,
                        'card_set_code' => $dataSet['set_code']
                    ],
                    [
                        'rarity_verbose' => $dataSet['set_rarity'],
                        'rarity_code' => $dataSet['set_rarity_code'],
                    ]
                );
            }
        }
    }
}
