<?php

namespace App\Console\Commands;

use App\Models\CardInstance;
use App\Models\OwnedCard;
use App\Models\Variant;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use function dd;
use function implode;

class MigrateVariantsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:variants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
//        CardInstance::chunk(1000, function (Collection $cardInstances) {
//            foreach ($cardInstances as $cardInstance){
//                /** @var CardInstance $cardInstance */
//
//                if($cardInstance->card->original === null){
//                    $masterCardInstance = $cardInstance;
//                } else {
//                    $masterCardInstances = $cardInstance->card->original->cardInstances
//                        ->where('set_id', $cardInstance->set_id)
//                        ->where('rarity_verbose', $cardInstance->rarity_verbose)
//                        ->where('card_set_code', $cardInstance->card_set_code)
//                        ->collect();
//
//
//                    if($masterCardInstances->count() > 1){
//                        $this->error("Multiple card instance ".implode(',', $masterCardInstances->pluck('id')->toArray())." for card instance {$cardInstance->id}.");
//                        continue;
//                    }
//
//                    if($masterCardInstances->count() === 0){
//                        $ci = CardInstance::create([
//                            'card_id' => $cardInstance->card->card_id,
//                            'set_id' => $cardInstance->set_id,
//                            'card_set_code' => $cardInstance->card_set_code,
//                            'rarity_verbose' => $cardInstance->rarity_verbose,
//                        ]);
//                        $masterCardInstances = collect([$ci]);
//                        $this->info("No master card instance for card instance {$cardInstance->id}. New ci {$ci->id} created.");
//                    }
//
//                    /** @var CardInstance $masterCardInstance */
//                    $masterCardInstance = $masterCardInstances->first();
//                }
//
//                $variant = Variant::firstOrCreate([
//                    'card_instance_id' => $masterCardInstance->id,
//                    'ygo_id' => $cardInstance->card->ygo_id,
//                ],[]);
//
//                if(!$variant->wasRecentlyCreated){
//                    $this->error("Master card instance {$masterCardInstance->id} for card instance {$cardInstance->id} skipped.");
//                }
//            }
//        });

//        OwnedCard::chunk(1000, function(Collection $ownedCards) {
//            foreach ($ownedCards as $ownedCard){
//                /** @var OwnedCard $ownedCard */
//                if($ownedCard->cardInstance->card->original === null){
//                    $variant = Variant::where('card_instance_id', $ownedCard->card_instance_id)
//                        ->where('ygo_id', $ownedCard->cardInstance->card->ygo_id)
//                        ->first();
//                } else {
//                    $masterCardInstances = $ownedCard->cardInstance->card->original->cardInstances
//                        ->where('set_id', $ownedCard->cardInstance->set_id)
//                        ->where('rarity_verbose', $ownedCard->cardInstance->rarity_verbose)
//                        ->where('card_set_code', $ownedCard->cardInstance->card_set_code)
//                        ->collect();
//
//
//                    if($masterCardInstances->count() > 1){
//                        $this->error("Multiple card instance ".implode(',', $masterCardInstances->pluck('id')->toArray())." for card instance {$cardInstance->id}.");
//                        continue;
//                    }
//
//                    $variant = Variant::where('card_instance_id', $masterCardInstances->first()->id)
//                        ->where('ygo_id', $ownedCard->cardInstance->card->original->ygo_id)
//                        ->first();
//                }
//
//
//                $ownedCard->variant_id = $variant->id;
//                $ownedCard->save();
//            }
//        });

        OwnedCard::chunk(1000, function(Collection $ownedCards) {
            foreach ($ownedCards as $ownedCard){

            }
        })
    }
}
