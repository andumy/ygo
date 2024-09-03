<?php

namespace App\Console\Commands;

use App\Models\CardInstance;
use App\Repositories\CardInstanceRepository;
use Illuminate\Console\Command;

class FillRarities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:rarities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(CardInstanceRepository $cardInstanceRepository)
    {
        $cardInstanceRepository->findMissingRarity()->each(function(CardInstance $cardInstance){
            $cardInstance->update([
                'rarity_code' => $cardInstance->rarity
            ]);
        });
    }
}
