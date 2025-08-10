<?php

namespace App\Livewire\Pages;

use App\Enums\Condition;
use App\Enums\Lang;
use App\Enums\Sale;
use App\Models\Variant;
use App\Repositories\OwnedCardRepository;
use App\Repositories\SetRepository;
use App\Repositories\VariantRepository;
use Illuminate\Support\Collection;
use Livewire\Component;
use function array_fill;
use function max;

class TradableCards extends Component
{

    public string $set = '';
    public array $variants = [];
    public array $changed = [];
    public string $message = '';
    public bool $onlyMissing = true;

    public int $totalCollectable = 0;
    public int $totalTradable = 0;
    public int $totalNotSet = 0;
    public int $totalListed = 0;

    public Collection $sets;

    private VariantRepository $variantRepository;
    private OwnedCardRepository $ownedCardRepository;
    private SetRepository $setRepository;

    public function boot(
        VariantRepository   $variantRepository,
        SetRepository       $setRepository,
        OwnedCardRepository $ownedCardRepository,
    )
    {
        $this->variantRepository = $variantRepository;
        $this->setRepository = $setRepository;
        $this->ownedCardRepository = $ownedCardRepository;

        $this->changeAvailableSets();
    }

    public function changeAvailableSets(){
        if($this->onlyMissing){
            $this->sets = $this->setRepository->allWithUnsetOwnedCards();
        } else {
            $this->sets = $this->setRepository->allWithOwnedCards();
        }
    }


    public function collect(int $variantId, string $lang, string $cond, int $isFirstEd)
    {
        $el = $this->variants
        [$variantId]
        [$lang]
        [$cond]
        [$isFirstEd];

        $this->variants
        [$variantId]
        [$lang]
        [$cond]
        [$isFirstEd] = [
            ...$el,
            'new_collectable' => $el['new_collectable'] + 1,
            'new_tradable' => $el['new_tradable'] + $el['not_set'] - 1,
            'collectable' => $el['new_collectable'] + 1,
            'tradable' => $el['new_tradable'] + $el['not_set'] - 1,
            'not_set' => 0,
        ];
        $this->variants
        [$variantId]
        [$lang]
        [$cond]
        [$isFirstEd]['was_changed'] = $this->checkIfWasChanged($variantId, $lang, $cond, $isFirstEd);

        $this->totalTradable += $el['tradable'] + $el['not_set'] - 1;
        $this->totalCollectable++;
        $this->totalNotSet -= $el['not_set'];

    }

    public function trade(int $variantId, string $lang, string $cond, int $isFirstEd)
    {
        $el = $this->variants
        [$variantId]
        [$lang]
        [$cond]
        [$isFirstEd];

        $this->variants
        [$variantId]
        [$lang]
        [$cond]
        [$isFirstEd] = [
            ...$el,
            'new_collectable' => $el['collectable'],
            'new_tradable' => $el['tradable'] + $el['not_set'],
            'collectable' => $el['collectable'],
            'tradable' => $el['tradable'] + $el['not_set'],
            'not_set' => 0,
        ];

        $this->variants
        [$variantId]
        [$lang]
        [$cond]
        [$isFirstEd]['was_changed'] = $this->checkIfWasChanged($variantId, $lang, $cond, $isFirstEd);

        $this->totalTradable += $el['tradable'] + $el['not_set'];
        $this->totalNotSet -= $el['not_set'];
    }

    private function checkIfWasChanged(int $variantId, string $lang, string $cond, int $isFirstEd): bool {
        return
            $this->changed[$variantId][$lang][$cond][$isFirstEd]['collectable'] !=
            $this->variants[$variantId][$lang][$cond][$isFirstEd]['new_collectable']
            ||
            $this->changed[$variantId][$lang][$cond][$isFirstEd]['tradable'] !=
            $this->variants[$variantId][$lang][$cond][$isFirstEd]['new_tradable']
            ||
            $this->changed[$variantId][$lang][$cond][$isFirstEd]['listed'] !=
            $this->variants[$variantId][$lang][$cond][$isFirstEd]['listed']
            ||
            $this->changed[$variantId][$lang][$cond][$isFirstEd]['not_set'] !=
            $this->variants[$variantId][$lang][$cond][$isFirstEd]['not_set'];
    }

    public function refresh()
    {
        $this->variants = [];

        $this->totalTradable = 0;
        $this->totalCollectable = 0;
        $this->totalNotSet = 0;
        $this->totalListed = 0;

        if($this->set === ''){
            $this->dispatch('$refresh');
            return;
        }

        foreach ($this->variantRepository->search(set: $this->set, onlyOwned: 1, excludeOrdered: true) as $variant) {
            /** @var Variant $variant */

            $ownedCardsWithAmount = $this->ownedCardRepository->fetchByVariantGroupByAllOverAmount($variant->id);
            foreach ($ownedCardsWithAmount as $ownedCard) {
                $currentElement = $this->variants
                [$variant->id]
                [$ownedCard->lang->value]
                [$ownedCard->cond->value]
                [(int)$ownedCard->is_first_edition] ?? [];

                $currentElement = [
                    'passcode' => $variant->variantCard->passcode,
                    'card_set_code' => $variant->cardInstance->card_set_code,
                    'card_name' => $variant->cardInstance->card->name,
                    'rarity' => $variant->cardInstance->rarity_verbose->value,
                    'collectable' => ($ownedCard->sale === Sale::IN_COLLECTION ? $ownedCard->amount : 0) + ($currentElement['collectable'] ?? 0),
                    'tradable' => ($ownedCard->sale === Sale::TRADE ? $ownedCard->amount : 0) + ($currentElement['tradable'] ?? 0),
                    'listed' => ($ownedCard->sale === Sale::LISTED ? $ownedCard->amount : 0) + ($currentElement['listed'] ?? 0),
                    'not_set' => ($ownedCard->sale === Sale::NOT_SET ? $ownedCard->amount : 0) + ($currentElement['not_set'] ?? 0),
                    'is_missing' => !$variant->isCollected,
                    'was_changed' => false
                ];

                $currentElement['new_collectable'] = $currentElement['collectable'];
                $currentElement['new_tradable'] = $currentElement['tradable'];

                if ($ownedCard->sale === Sale::TRADE) {
                    $this->totalTradable += $ownedCard->amount;
                }

                if ($ownedCard->sale === Sale::IN_COLLECTION) {
                    $this->totalCollectable += $ownedCard->amount;
                }

                if ($ownedCard->sale === Sale::NOT_SET) {
                    $this->totalNotSet += $ownedCard->amount;
                }

                if ($ownedCard->sale === Sale::LISTED) {
                    $this->totalListed += $ownedCard->amount;
                }

                $this->variants
                [$variant->id]
                [$ownedCard->lang->value]
                [$ownedCard->cond->value]
                [(int)$ownedCard->is_first_edition] = $currentElement;

                $this->changed
                [$variant->id]
                [$ownedCard->lang->value]
                [$ownedCard->cond->value]
                [(int)$ownedCard->is_first_edition] = [
                    'collectable' => $currentElement['collectable'],
                    'tradable' => $currentElement['tradable'],
                    'listed' => $currentElement['listed'],
                    'not_set' => $currentElement['not_set'],
                ];
            }
        }
        $this->dispatch('$refresh');
    }

    public function revalidate(int $variantId, string $lang, string $cond, int $isFirstEd)
    {
        $ownedCard = $this->variants[$variantId][$lang][$cond][$isFirstEd];
        $deltaTradable = $ownedCard['new_tradable'] - $ownedCard['tradable'];
        $deltaCollectable = $ownedCard['new_collectable'] - $ownedCard['collectable'];

        $totalChange = $deltaCollectable + $deltaTradable;
        $this->variants[$variantId][$lang][$cond][$isFirstEd]['not_set'] -= $totalChange;
        $this->variants[$variantId][$lang][$cond][$isFirstEd]['tradable'] = $ownedCard['new_tradable'];
        $this->variants[$variantId][$lang][$cond][$isFirstEd]['collectable'] = $ownedCard['new_collectable'];
        $this->variants[$variantId][$lang][$cond][$isFirstEd]['was_changed'] = $this->checkIfWasChanged($variantId, $lang, $cond, $isFirstEd);

        $this->totalTradable += $deltaTradable;
        $this->totalCollectable += $deltaCollectable;
        $this->totalNotSet -= $totalChange;
    }

    public function autofill()
    {
        foreach ($this->variants as $variantId => $variantArray) {
            foreach ($variantArray as $lang => $langArray) {
                foreach ($langArray as $cond => $condArray) {
                    foreach ($condArray as $isFirstEd => $ownedCard) {

                        if($this->variants[$variantId][$lang][$cond][$isFirstEd]['not_set'] === 0){
                            continue;
                        }

                        if($this->variants[$variantId][$lang][$cond][$isFirstEd]['is_missing']){
                            $this->collect($variantId, $lang, $cond, $isFirstEd);
                        } else {
                            $this->trade($variantId, $lang, $cond, $isFirstEd);
                        }
                    }
                }
            }
        }
    }

    public function save()
    {
        $batch = $this->ownedCardRepository->fetchNextBatch();
        foreach ($this->variants as $variantId => $variantArray) {
            foreach ($variantArray as $lang => $langArray) {
                foreach ($langArray as $cond => $condArray) {
                    foreach ($condArray as $isFirstEd => $ownedCard) {

                        if(!$ownedCard['was_changed']){
                            continue;
                        }

                        $this->ownedCardRepository->resetSale(
                            variantId: $variantId,
                            lang: Lang::from($lang),
                            condition: Condition::from($cond),
                            isFirstEdition: (bool)$isFirstEd
                        );

                        $newStructure = [
                            ...array_fill(0, max((int)$ownedCard['new_tradable'], 0), Sale::TRADE),
                            ...array_fill(0, max((int)$ownedCard['listed'], 0), Sale::LISTED),
                            ...array_fill(0, max((int)$ownedCard['new_collectable'], 0), Sale::IN_COLLECTION),
                            ...array_fill(0, max((int)$ownedCard['not_set'], 0), Sale::NOT_SET),
                        ];

                        foreach ($newStructure as $sale) {
                            $this->ownedCardRepository->create(
                                variantId: $variantId,
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
        if($this->onlyMissing){
            $this->sets = $this->setRepository->allWithUnsetOwnedCards();
        } else {
            $this->sets = $this->setRepository->allWithOwnedCards();
        }
        $this->set = $this->sets->first()?->name ?? '';
        $this->refresh();
    }

    public function render()
    {
        return view('livewire.tradable-cards');
    }
}
