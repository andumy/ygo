<?php

namespace App\Livewire;

use App\Enums\Condition;
use App\Enums\Lang;
use App\Enums\Sale;
use App\Repositories\CardInstanceRepository;
use App\Repositories\OwnedCardRepository;
use App\Repositories\SetRepository;
use Illuminate\Support\Collection;
use Livewire\Component;
use function array_fill;
use function max;

class TradableCards extends Component
{

    public string $set = '';
    public array $cardInstances = [];
    public string $message = '';

    public int $totalCollectable = 0;
    public int $totalTradable = 0;
    public int $totalNotSet = 0;


    public Collection $sets;

    private CardInstanceRepository $cardInstanceRepository;
    private OwnedCardRepository $ownedCardRepository;
    private SetRepository $setRepository;

    public function boot(
        CardInstanceRepository $cardInstanceRepository,
        SetRepository $setRepository,
        OwnedCardRepository $ownedCardRepository,
    )
    {
        $this->cardInstanceRepository = $cardInstanceRepository;
        $this->setRepository = $setRepository;
        $this->ownedCardRepository = $ownedCardRepository;

        $this->sets = $this->setRepository->allWithUnsetOwnedCards();
    }
    public function refresh(){
        $this->cardInstances = [];
        $this->totalTradable = 0;
        $this->totalCollectable = 0;
        $this->totalNotSet = 0;

        foreach ($this->cardInstanceRepository->search(set: $this->set, onlyOwned: 1, excludeOrdered: true) as $cardInstance){
            $ownedCards = $this->ownedCardRepository->fetchByInstanceGroupByAllOverAmount($cardInstance->id);

            foreach ($ownedCards as $ownedCard){
                $currentElement = $this->cardInstances
                    [$cardInstance->id]
                    [$ownedCard->lang->value]
                    [$ownedCard->cond->value]
                    [(int)$ownedCard->is_first_edition] ?? [];

                $currentElement = [
                    'ygo_id' => $cardInstance->card->ygo_id,
                    'card_set_code' => $cardInstance->card_set_code,
                    'card_name' => $cardInstance->card->name,
                    'rarity' => $cardInstance->rarity_verbose,
                    'collectable' => ($ownedCard->sale === Sale::IN_COLLECTION ? $ownedCard->amount : 0) + ($currentElement['collectable'] ?? 0),
                    'tradable' => ($ownedCard->sale === Sale::TRADE ? $ownedCard->amount : 0) + ($currentElement['tradable'] ?? 0),
                    'not_set' => ($ownedCard->sale === Sale::NOT_SET ? $ownedCard->amount : 0) + ($currentElement['not_set'] ?? 0),

                ];

                $currentElement['new_collectable'] = $currentElement['collectable'];
                $currentElement['new_tradable'] = $currentElement['tradable'];

                if($ownedCard->sale === Sale::TRADE){
                    $this->totalTradable += $ownedCard->amount;
                }

                if($ownedCard->sale === Sale::IN_COLLECTION){
                    $this->totalCollectable += $ownedCard->amount;
                }

                if($ownedCard->sale === Sale::NOT_SET){
                    $this->totalNotSet += $ownedCard->amount;
                }

                $this->cardInstances
                    [$cardInstance->id]
                    [$ownedCard->lang->value]
                    [$ownedCard->cond->value]
                    [(int)$ownedCard->is_first_edition] = $currentElement;
            }

        }
    }

    public function revalidate(){
        $this->totalTradable = 0;
        $this->totalCollectable = 0;
        $this->totalNotSet = 0;

        foreach($this->cardInstances as $cardInstanceId => $cardInstanceArray) {
            foreach ($cardInstanceArray as $lang => $langArray) {
                foreach ($langArray as $cond => $condArray) {
                    foreach ($condArray as $isFirstEd => $ownedCard) {
                        $deltaTradable = $ownedCard['new_tradable'] - $ownedCard['tradable'];
                        $deltaCollectable = $ownedCard['new_collectable'] - $ownedCard['collectable'];

                        $totalChange = $deltaCollectable + $deltaTradable;
                        $this->cardInstances[$cardInstanceId][$lang][$cond][$isFirstEd]['not_set'] -= $totalChange;
                        $this->cardInstances[$cardInstanceId][$lang][$cond][$isFirstEd]['tradable'] = $ownedCard['new_tradable'];
                        $this->cardInstances[$cardInstanceId][$lang][$cond][$isFirstEd]['collectable'] = $ownedCard['new_collectable'];

                        $this->totalTradable += $ownedCard['new_tradable'];
                        $this->totalCollectable += $ownedCard['new_collectable'];
                        $this->totalNotSet += $this->cardInstances[$cardInstanceId][$lang][$cond][$isFirstEd]['not_set'];
                    }
                }
            }
        }
    }

    public function autofill(){
        $this->totalTradable = 0;
        $this->totalCollectable = 0;
        $this->totalNotSet = 0;

        foreach($this->cardInstances as $cardInstanceId => $cardInstanceArray) {
            foreach ($cardInstanceArray as $lang => $langArray) {
                foreach ($langArray as $cond => $condArray) {
                    foreach ($condArray as $isFirstEd => $ownedCard) {
                        if($ownedCard['collectable'] === 0){
                            $ownedCard['new_tradable'] = $ownedCard['tradable'] + $ownedCard['not_set'] - 1;
                            $ownedCard['new_collectable'] = 1;
                        } else {
                            $ownedCard['new_tradable'] = $ownedCard['tradable'] + $ownedCard['not_set'];
                        }

                        $this->cardInstances[$cardInstanceId][$lang][$cond][$isFirstEd]['not_set'] = 0;
                        $this->cardInstances[$cardInstanceId][$lang][$cond][$isFirstEd]['tradable'] = $ownedCard['new_tradable'];
                        $this->cardInstances[$cardInstanceId][$lang][$cond][$isFirstEd]['collectable'] = $ownedCard['new_collectable'];
                        $this->cardInstances[$cardInstanceId][$lang][$cond][$isFirstEd]['new_tradable'] = $ownedCard['new_tradable'];
                        $this->cardInstances[$cardInstanceId][$lang][$cond][$isFirstEd]['new_collectable'] = $ownedCard['new_collectable'];

                        $this->totalTradable += $ownedCard['new_tradable'];
                        $this->totalCollectable += $ownedCard['new_collectable'];
                    }
                }
            }
        }
    }

    public function save(){
        $batch = $this->ownedCardRepository->fetchNextBatch();
        foreach($this->cardInstances as $cardInstanceId => $cardInstanceArray) {
            foreach ($cardInstanceArray as $lang => $langArray) {
                foreach ($langArray as $cond => $condArray) {
                    foreach ($condArray as $isFirstEd => $ownedCard) {

                        $this->ownedCardRepository->resetSale(
                            cardInstanceId: $cardInstanceId,
                            lang: Lang::from($lang),
                            condition: Condition::from($cond),
                            isFirstEdition: (bool)$isFirstEd
                        );

                        $newStructure = [
                            ...array_fill(0, max((int)$ownedCard['new_tradable'], 0), Sale::TRADE),
                            ...array_fill(0, max((int)$ownedCard['new_collectable'], 0), Sale::IN_COLLECTION),
                            ...array_fill(0, max((int)$ownedCard['not_set'], 0), Sale::NOT_SET),
                        ];

                        foreach($newStructure as $sale){
                            $this->ownedCardRepository->create(
                                cardInstanceId: $cardInstanceId,
                                batch: $batch,
                                lang: Lang::from($lang),
                                condition: Condition::from($cond),
                                isFirstEdition: (bool)$isFirstEd,
                                sale: $sale
                            );
                        }
                    }
                }
            }
        }

        $this->message = 'Set saved';
        $this->sets = $this->setRepository->allWithUnsetOwnedCards();
        $this->set = $this->sets->first()?->name ?? '';
        $this->refresh();
    }

    public function render()
    {
        return view('livewire.tradable-cards');
    }
}
