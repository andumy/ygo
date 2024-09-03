<?php

namespace App\Livewire;

use App\Models\CardInstance;
use App\Models\OwnedCard;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\OwnedCardRepository;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Cards extends Component
{
    use WithPagination;

    public string $code = '';
    public string $search = '';
    public string $message = '';
    private CardRepository $cardRepository;
    private CardInstanceRepository $cardInstanceRepository;
    private OwnedCardRepository $ownedCardRepository;

    public function boot(
        CardRepository $cardRepository,
        CardInstanceRepository $cardInstanceRepository,
        OwnedCardRepository $ownedCardRepository
    )
    {
        $this->cardRepository = $cardRepository;
        $this->cardInstanceRepository = $cardInstanceRepository;
        $this->ownedCardRepository = $ownedCardRepository;
    }

    public function addCard()
    {
        $this->message = '';
        if($this->code === '') return;

        /** @var Collection<CardInstance> $cardInstance */
        $cardInstance = $this->cardInstanceRepository->findBySetCode($this->code);
        /** @var OwnedCard $ownCard */
        $ownCard = $cardInstance->first()->ownedCard;

        if($ownCard) {
            $ownCard->amount += 1;
            $ownCard->save();
            $this->message =
                $ownCard->cardInstance->card->name . ' variant ' .
                $ownCard->cardInstance->card_set_code . ' incremented to ' .
                $ownCard->amount;
            return;
        }
        $ownCard = $this->ownedCardRepository->create([
            'card_instance_id' => $cardInstance->first()->id,
            'amount' => 1,
        ]);
        $this->message =
            $ownCard->cardInstance->card->name . ' variant ' .
            $ownCard->cardInstance->card_set_code . ' was added ';
    }

    public function fetchCards()
    {
    }

    public function render()
    {
        return view('livewire.cards', [
            'cards' => $this->cardRepository->paginate($this->search, 50)
        ]);
    }
}
