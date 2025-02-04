<?php

namespace App\Livewire;

use App\Repositories\OrderRepository;
use App\Repositories\OwnedCardRepository;
use Illuminate\Support\Collection;
use Livewire\Component;

class Orders extends Component
{
    private OrderRepository $orderRepository;
    private OwnedCardRepository $ownedCardRepository;

    public string $message = '';
    public string $orderName;
    public ?int $orderId = null;
    public Collection $orders;

    public function boot(
        OrderRepository $orderRepository,
        OwnedCardRepository $ownedCardRepository,
    )
    {
        $this->orderRepository = $orderRepository;
        $this->ownedCardRepository = $ownedCardRepository;
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

        $this->ownedCardRepository->shipOrder($this->orderId);

        $this->message = 'Order shipped';

        $this->orderRepository->delete($this->orderId);
        $this->orderId = null;
    }

    public function render()
    {
        $this->orders = $this->orderRepository->all();
        $orderedCards = [];

        if($this->orderId) {
            $orderedCards = $this->ownedCardRepository->fetchByOrderWithAmount($this->orderId);
        }

        return view('livewire.orders', [
            'orderedCards' => $orderedCards
        ]);
    }
}
