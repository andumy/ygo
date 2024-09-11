<?php

namespace App\Livewire;

use App\Enums\AddCardStatuses;
use App\Models\CardInstance;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\OrderedCardRepository;
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
    private OrderedCardRepository $orderedCardRepository;
    private CardService $cardService;
    private SetRepository $setRepository;

    public function boot(
        CardRepository $cardRepository,
        CardInstanceRepository $cardInstanceRepository,
        OwnedCardRepository $ownedCardRepository,
        CardService $cardService,
        SetRepository $setRepository,
        OrderRepository $orderRepository,
        OrderedCardRepository $orderedCardRepository,
    )
    {
        $this->cardRepository = $cardRepository;
        $this->cardInstanceRepository = $cardInstanceRepository;
        $this->ownedCardRepository = $ownedCardRepository;
        $this->cardService = $cardService;
        $this->setRepository = $setRepository;
        $this->orderRepository = $orderRepository;
        $this->orderedCardRepository = $orderedCardRepository;
    }

    public function updateStock(int $instanceId): void
    {
        $instance = $this->cardInstanceRepository->findById($instanceId);

        $ownAmount = array_key_exists($instanceId,$this->ownedCards) ?
            (int)$this->ownedCards[$instanceId] : 0;
        $orderAmount = array_key_exists($instanceId,$this->orderedCards) ?
            (int)$this->orderedCards[$instanceId] : 0;
        $orderId = array_key_exists($instanceId,$this->orderId) ?
            (int)$this->orderId[$instanceId] : null;

        if($orderId){
            $this->cardService->updateCardStockFromInstance(
                cardInstance: $instance,
                orderId: $orderId,
                orderAmount: $orderAmount,
            );
        }

        $this->cardService->updateCardStockFromInstance(
            cardInstance: $instance,
            ownAmount: $ownAmount
        );

    }

    public function mount(): void
    {
        $this->sets = $this->setRepository->all();
        foreach ($this->sets as $set) {
            $this->fillBySet[$set->name] = [
                'total' => $this->cardRepository->count('', $set->name),
                'owned' => $this->cardRepository->countOwnedAndOrdered('', $set->name)
            ];
        }
    }

    public function addCard(): void
    {
        $this->message = '';
        if($this->code === '') return;

        $response = $this->cardService->updateCardStock(
            code: $this->code,
            rarity: $this->rarity
        );

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
        $owned = $this->cardRepository->countOwnedAndOrdered($this->search, $this->set, $this->hideOwned);
        $total = $this->cardRepository->count($this->search, $this->set, $this->hideOwned);
        $this->orders = $this->orderRepository->all();
        return view('livewire.cards', [
            'cards' => $this->cardRepository->paginate($this->search, $this->set, $this->hideOwned, 50),
            'total' => $total,
            'owned' => $owned,
            'amountOfCards' =>
                $this->ownedCardRepository->countAmountOfCards() + $this->orderedCardRepository->countAmountOfCards(),
            'percentage' => $total != 0 ? round($owned / $total * 100,2) : 0
        ]);
    }
}
