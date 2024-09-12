<?php

namespace App\Livewire;

use App\Enums\AddCardStatuses;
use App\Models\CardInstance;
use App\Repositories\CardRepository;
use App\Repositories\OrderedCardRepository;
use App\Repositories\OrderRepository;
use App\Services\CardService;
use Illuminate\Support\Collection;
use Livewire\Component;
use function array_unique;
use function count;

class Orders extends Component
{
    private OrderRepository $orderRepository;
    private OrderedCardRepository $orderedCardRepository;
    private CardService $cardService;
    private CardRepository $cardRepository;

    public string $message = '';
    public string $orderName;
    public ?int $orderId = null;
    public Collection $orders;

    public function boot(
        OrderRepository $orderRepository,
        CardRepository $cardRepository,
        OrderedCardRepository $orderedCardRepository,
        CardService $cardService
    )
    {
        $this->orderRepository = $orderRepository;
        $this->cardRepository = $cardRepository;
        $this->orderedCardRepository = $orderedCardRepository;
        $this->cardService = $cardService;
    }


    public function addOrder()
    {
        $this->orderRepository->firstOrCreate($this->orderName);
        $this->message = 'Order added';
    }

    public function refresh()
    {
    }

    public function shipOrder()
    {
        $newCards = [];
        $newPhysicalCards = 0;

        $duplicateCards = [];
        $duplicatePhysicalCards = 0;

        $duplicateOrderedCards = [];
        $duplicatePhysicalOrderedCards = 0;

        $addedCards = [];
        $totalPhysicalAddedCardsToOwned = 0;
        $orderedCards = $this->orderedCardRepository->getByOrderId($this->orderId);

        foreach ($orderedCards as $orderedCard) {
            $addedCards[] = $orderedCard->cardInstance->card->id;
            $totalPhysicalAddedCardsToOwned += $orderedCard->amount;

            $response = $this->cardService->updateCardStockFromInstance(
                cardInstance: $orderedCard->cardInstance,
                ownAmount: $orderedCard->amount
            );

            switch($response->status){
                case AddCardStatuses::INCREMENT:
                    $duplicateCards[] = $orderedCard->cardInstance->card->id;
                    $duplicatePhysicalCards += $orderedCard->amount;
                    break;
                case AddCardStatuses::NEW_CARD:
                    if(
                        $orderedCard
                            ->cardInstance
                            ->card
                            ->cardInstances->filter(function (CardInstance $ci) use ($orderedCard){
                                return $ci->orderedCards()
                                    ->where('order_id','!=', $orderedCard->order_id)
                                    ->exists();
                            })->isEmpty()
                    ){
                        $newCards[] = $orderedCard->cardInstance->card->id;
                        $newPhysicalCards += $orderedCard->amount;
                    } else {
                        $duplicateOrderedCards[] = $orderedCard->cardInstance->card->id;
                        $duplicatePhysicalOrderedCards += $orderedCard->amount;
                    }
                    break;
                default:
                    break;
            }
        }

        $totalAddedCardsToOwned = count(array_unique($addedCards));
        $this->orderRepository->delete($this->orderId);
        $this->orderId = null;

        $this->message = "
            A total of $totalAddedCardsToOwned cards were added ($totalPhysicalAddedCardsToOwned physical),
            out of which ".count(array_unique($newCards))." ($newPhysicalCards physical) were new, ".
            count(array_unique($duplicateCards)) ." ($duplicatePhysicalCards physical) were duplicates and ".
            count(array_unique($duplicateOrderedCards)) ." ($duplicatePhysicalOrderedCards physical) were already ordered.
        ";
    }

    public function render()
    {
        $this->orders = $this->orderRepository->all();
        $cards = [];

        if($this->orderId) {
            $cards = $this->cardRepository->getForOrder($this->orderId);
        }

        return view('livewire.orders', [
            'cards' => $cards
        ]);
    }
}
