<?php

namespace App\Livewire;

use App\Enums\AddCardStatuses;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\OwnedCardRepository;
use App\Services\CardService;
use Livewire\Component;
use Livewire\WithPagination;

class Cards extends Component
{
    use WithPagination;

    public string $code = '';
    public string $rarity = '';
    public array $rarities = [];
    public string $search = '';
    public string $message = '';
    private CardRepository $cardRepository;
    private CardInstanceRepository $cardInstanceRepository;
    private OwnedCardRepository $ownedCardRepository;
    private CardService $cardService;

    public function boot(
        CardRepository $cardRepository,
        CardInstanceRepository $cardInstanceRepository,
        OwnedCardRepository $ownedCardRepository,
        CardService $cardService
    )
    {
        $this->cardRepository = $cardRepository;
        $this->cardInstanceRepository = $cardInstanceRepository;
        $this->ownedCardRepository = $ownedCardRepository;
        $this->cardService = $cardService;
    }

    public function addCard()
    {

        $this->message = '';
        if($this->code === '') return;

        $response = $this->cardService->addCard($this->code, $this->rarity);

        switch ($response->status) {
            case AddCardStatuses::MULTIPLE_OPTIONS:
                $this->message = 'Select the rarity';
                $this->rarities = $response->rarities;
                break;
            case AddCardStatuses::NEW_CARD:
                $this->message = 'New card added: ' . $response->cardName;
                $this->rarities = [];
                $this->rarity = '';
                break;
            case AddCardStatuses::INCREMENT:
                $this->message =  $response->cardName . ' incremented';
                $this->rarities = [];
                $this->rarity = '';
                break;
        }

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
