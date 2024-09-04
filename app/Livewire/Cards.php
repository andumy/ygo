<?php

namespace App\Livewire;

use App\Enums\AddCardStatuses;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\OwnedCardRepository;
use App\Repositories\SetRepository;
use App\Services\CardService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use function var_dump;

class Cards extends Component
{
    use WithPagination;

    public string $code = '';
    public string $set = '';
    public string $rarity = '';
    public array $rarities = [];
    public array $fillBySet = [];
    public Collection $sets;
    public string $search = '';
    public string $message = '';
    private CardRepository $cardRepository;
    private CardInstanceRepository $cardInstanceRepository;
    private OwnedCardRepository $ownedCardRepository;
    private CardService $cardService;
    private SetRepository $setRepository;

    public function boot(
        CardRepository $cardRepository,
        CardInstanceRepository $cardInstanceRepository,
        OwnedCardRepository $ownedCardRepository,
        CardService $cardService,
        SetRepository $setRepository,
    )
    {
        $this->cardRepository = $cardRepository;
        $this->cardInstanceRepository = $cardInstanceRepository;
        $this->ownedCardRepository = $ownedCardRepository;
        $this->cardService = $cardService;
        $this->setRepository = $setRepository;
    }

    public function mount() {
        $this->sets = $this->setRepository->all();
        foreach ($this->sets as $set) {
            $this->fillBySet[$set->name] = [
                'total' => $this->cardRepository->count('', $set->name),
                'owned' => $this->cardRepository->countOwned('', $set->name)
            ];
        }
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
            case AddCardStatuses::NOT_FOUND:
                $this->message =  'Not Found';
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
            'cards' => $this->cardRepository->paginate($this->search, $this->set, 50),
            'total' => $this->cardRepository->count($this->search, $this->set),
            'owned' => $this->cardRepository->countOwned($this->search, $this->set),
            'amountOfCards' => $this->ownedCardRepository->countAmountOfCards(),
        ]);
    }
}
