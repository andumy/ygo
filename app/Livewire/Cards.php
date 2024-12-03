<?php

namespace App\Livewire;

use App\Enums\AddCardStatuses;
use App\Models\Card;
use App\Models\CardInstance;
use App\Models\OrderedCard;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\OrderedCardRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OwnedCardRepository;
use App\Repositories\PriceRepository;
use App\Repositories\SetRepository;
use App\Services\CardService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use function array_key_exists;
use function dd;
use function round;

class Cards extends Component
{
    use WithPagination;

    public int $ownedFilter = 0; // 0 = all, 1 = owned, -1 = not owned

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
    public array $prices = [];
    public array $orderedCards = [];
    public array $orderId = [];

    private CardRepository $cardRepository;
    private OrderRepository $orderRepository;
    private CardInstanceRepository $cardInstanceRepository;
    private OwnedCardRepository $ownedCardRepository;
    private OrderedCardRepository $orderedCardRepository;
    private CardService $cardService;
    private SetRepository $setRepository;
    private PriceRepository $priceRepository;

    public function boot(
        CardRepository $cardRepository,
        CardInstanceRepository $cardInstanceRepository,
        OwnedCardRepository $ownedCardRepository,
        CardService $cardService,
        SetRepository $setRepository,
        OrderRepository $orderRepository,
        OrderedCardRepository $orderedCardRepository,
        PriceRepository $priceRepository,
    )
    {
        $this->cardRepository = $cardRepository;
        $this->cardInstanceRepository = $cardInstanceRepository;
        $this->ownedCardRepository = $ownedCardRepository;
        $this->cardService = $cardService;
        $this->setRepository = $setRepository;
        $this->orderRepository = $orderRepository;
        $this->orderedCardRepository = $orderedCardRepository;
        $this->priceRepository = $priceRepository;
    }

    public function updateOwn(int $instanceId): void
    {
        $instance = $this->cardInstanceRepository->findById($instanceId);
        $owned = $this->ownedCards[$instanceId];

        $this->cardService->updateCardStockFromInstance(
            cardInstance: $instance,
            ownAmount: $owned
        );
    }

    public function updateOrders(int $instanceId): void
    {
        $instance = $this->cardInstanceRepository->findById($instanceId);

        $orders = $this->orderedCards[$instanceId];

        foreach ($orders as $orderId => $orderAmount){
            $this->cardService->updateCardStockFromInstance(
                cardInstance: $instance,
                orderId: $orderId,
                orderAmount: $orderAmount,
            );
        }
    }

    public function updatePrice(int $instanceId): void
    {
        if($this->prices[$instanceId] == ""){
            $this->priceRepository->deleteForCardInstance($instanceId);
            return;
        }
        $this->priceRepository->updateOrCreate(
            find: ['card_instance_id' => $instanceId],
            data: [
                'price' => $this->prices[$instanceId],
                'date' => Carbon::now()
            ]
        );
    }

    public function addOrder(int $instanceId): void
    {
        $instance = $this->cardInstanceRepository->findById($instanceId);

        $orderAmount = $this->orderedCards[$instanceId][0];
        $orderId = $this->orderId[$instanceId];

        $this->cardService->updateCardStockFromInstance(
            cardInstance: $instance,
            orderId: $orderId,
            orderAmount: $orderAmount,
        );
    }

    public function addCard(): void
    {
        $this->message = '';
        if($this->code === '') return;


        $cardInstances = $this->cardInstanceRepository->findBySetCodeAndRarity($this->code, $this->rarity);

        $response = $this->cardService->updateCardStock(
            code: $this->code,
            option: $cardInstances,
            shouldIncrease: true
        );

        switch ($response->status) {
            case AddCardStatuses::MULTIPLE_OPTIONS:
                $this->message = 'Select the rarity';
                $this->rarities = $response->options->pluck('rarity_verbose')->toArray();
                break;
            case AddCardStatuses::NEW_CARD:
                $this->message = 'New card added: ' . $response->options->first()->card->name;
                $this->rarities = [];
                $this->rarity = '';
                break;
            case AddCardStatuses::INCREMENT:
                $this->message =  $response->options->first()->card->name . ' incremented';
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

    public function mount(): void
    {
        $this->sets = $this->setRepository->all();
//        foreach ($this->sets as $set) {
//            $this->fillBySet[$set->name] = [
//                'total' => $this->cardRepository->count(set: $set->name),
//                'owned' => $this->cardRepository->countOwnedAndOrderedInsideSet(set: $set->name)
//            ];
//        }
    }

    public function render()
    {
        /** @var Collection<Card> $cards */
        $cards = $this->cardRepository->paginate($this->search, $this->set, 45, $this->ownedFilter);
        foreach ($cards as $card){
            foreach ($card->cardInstances as $cardInstance) {
                $this->ownedCards[$cardInstance->id] = $cardInstance->ownedCard?->amount ?? 0;
                $this->orderedCards[$cardInstance->id] = [];
                $this->prices[$cardInstance->id] = $cardInstance->price->price ?? 0;
                foreach ($cardInstance->orderedCards as $orderedCard){
                    $this->orderedCards[$cardInstance->id][$orderedCard->order_id] = $orderedCard->amount ?? 0;
                }
            }
        }
        $owned = $this->cardRepository->count($this->search, $this->set, 1);
        $total = $this->cardRepository->count($this->search, $this->set, $this->ownedFilter);

        $ownedInstances = $this->cardInstanceRepository->count($this->search, $this->set, 1);
        $totalInstances = $this->cardInstanceRepository->count($this->search, $this->set, $this->ownedFilter);

        $totalPrice = $this->cardInstanceRepository->priceForOwnOrOrder();
        $this->orders = $this->orderRepository->all();
        return view('livewire.cards', [
            'owned' => $owned,
            'total' => $total,
            'percentage' => $total != 0 ? round($owned / $total * 100,2) : 0,
            'ownedInstances' => $ownedInstances,
            'totalInstances' => $totalInstances,
            'percentageInstances' => $totalInstances != 0 ? round($ownedInstances / $totalInstances * 100,2) : 0,
            'cards' => $cards,
            'totalPrice' => $totalPrice,
            'amountOfCards' =>
                $this->ownedCardRepository->countAmountOfCards() + $this->orderedCardRepository->countAmountOfCards(),
            'setCode' => $this->setRepository->findByName($this->set)?->code ?? '',
        ]);
    }
}
