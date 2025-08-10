<?php

namespace App\Livewire\Pages;

use App\Enums\Condition;
use App\Enums\Lang;
use App\Models\Variant;
use App\Repositories\OwnedCardRepository;
use App\Services\CardService;
use Livewire\Component;

class SingleVariant extends Component
{
    public Variant $variant;
    public array $ownedCards = [];
    public string $message = '';

    private OwnedCardRepository $ownedCardRepository;
    private CardService $cardService;

    public function boot(
        OwnedCardRepository $ownedCardRepository,
        CardService $cardService,
    )
    {
        $this->ownedCardRepository = $ownedCardRepository;
        $this->cardService = $cardService;
    }


    public function mount(Variant $variant)
    {
        $this->variant = $variant;
    }


    public function updateOwn(): void
    {
        $batch = $this->ownedCardRepository->fetchNextBatch();

        foreach ($this->ownedCards as $lang => $langArray) {
            foreach ($langArray as $cond => $condArray) {
                foreach ($condArray as $isFirstEdition => $amount) {
                    if($amount === ""){
                        continue;
                    }
                    $this->cardService->updateCardStockFromInstance(
                        variant: $this->variant,
                        batch: $batch,
                        lang: Lang::from($lang),
                        condition: Condition::from($cond),
                        amount: (int)$amount,
                        isFirstEdition: $isFirstEdition,
                    );
                }
            }
        }

        $this->message = 'Stock updated successfully.';
    }

    public function render()
    {
        $this->ownedCards = [];

        foreach ($this->ownedCardRepository->fetchByVariantGroupByAllOverAmount($this->variant->id) as $ownedCardWithAmount){
            if(
                $this->ownedCards
                [$ownedCardWithAmount->lang->value]
                [$ownedCardWithAmount->cond->value]
                [(int)$ownedCardWithAmount->is_first_edition] ?? null
            ) {
                $this->ownedCards
                [$ownedCardWithAmount->lang->value]
                [$ownedCardWithAmount->cond->value]
                [(int)$ownedCardWithAmount->is_first_edition] += $ownedCardWithAmount->amount;
            } else {
                $this->ownedCards
                [$ownedCardWithAmount->lang->value]
                [$ownedCardWithAmount->cond->value]
                [(int)$ownedCardWithAmount->is_first_edition] = $ownedCardWithAmount->amount ?? 0;
            }
        }

        return view('livewire.single-variant');
    }
}
