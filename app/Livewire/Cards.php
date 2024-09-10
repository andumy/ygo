<?php

namespace App\Livewire;

use App\Enums\AddCardStatuses;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OwnedCardRepository;
use App\Repositories\SetRepository;
use App\Services\CardService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use function array_key_exists;

class Cards extends Component
{
    use WithPagination;

    public bool $hideOwned = false;

    public string $code = '';
    public string $set = '';
    public string $rarity = '';
    public array $rarities = [];
    public array $fillBySet = [];
    public Collection $sets;
    public string $search = '';
    public string $message = '';

    public Collection $orders;
    public array $ownedCards = [];
    public array $orderedCards = [];
    public array $orderId = [];

    private CardRepository $cardRepository;
    private OrderRepository $orderRepository;
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
        OrderRepository $orderRepository,
    )
    {
        $this->cardRepository = $cardRepository;
        $this->cardInstanceRepository = $cardInstanceRepository;
        $this->ownedCardRepository = $ownedCardRepository;
        $this->cardService = $cardService;
        $this->setRepository = $setRepository;
        $this->orderRepository = $orderRepository;
    }

    public function updateStock(int $instanceId){
        $instance = $this->cardInstanceRepository->findById($instanceId);
        if($ownedCard = $instance->ownedCard){
            $ownedCard->amout += array_key_exists($instanceId,$this->ownedCards) ?
                (int)$this->ownedCards[$instanceId] : 0;
            $ownedCard->order_amount += array_key_exists($instanceId,$this->orderedCards) ?
                (int)$this->orderedCards[$instanceId] : 0;
            $ownedCard->order_id = array_key_exists($instanceId,$this->orderId) ?
                (int)$this->orderId[$instanceId] : null;
            $ownedCard->save();

            $this->message = 'Amount updated';
            return;
        }

        $this->ownedCardRepository->firstOrCreate(
            $instanceId,
            array_key_exists($instanceId,$this->ownedCards) ?
                (int)$this->ownedCards[$instanceId] : 0,
            array_key_exists($instanceId,$this->orderedCards) ?
                (int)$this->orderedCards[$instanceId] : 0,
            array_key_exists($instanceId,$this->orderId) ?
                (int)$this->orderId[$instanceId] : null
        );

        $this->message = 'Amount updated';
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

    public function refresh()
    {
    }

    public function render()
    {
        $owned = $this->cardRepository->countOwned($this->search, $this->set, $this->hideOwned);
        $total = $this->cardRepository->count($this->search, $this->set, $this->hideOwned);
        $this->orders = $this->orderRepository->all();
        return view('livewire.cards', [
            'cards' => $this->cardRepository->paginate($this->search, $this->set, $this->hideOwned, 50),
            'total' => $total,
            'owned' => $owned,
            'amountOfCards' => $this->ownedCardRepository->countAmountOfCards(),
            'percentage' => $total != 0 ? round($owned / $total * 100,2) : 0
        ]);
    }
}
