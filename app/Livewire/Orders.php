<?php

namespace App\Livewire;

use App\Repositories\CardRepository;
use App\Repositories\OrderRepository;
use Illuminate\Support\Collection;
use Livewire\Component;

class Orders extends Component
{
    private OrderRepository $orderRepository;
    private CardRepository $cardRepository;

    public string $message = '';
    public string $orderName;
    public ?int $order = null;
    public Collection $orders;

    public function boot(
        OrderRepository $orderRepository,
        CardRepository $cardRepository
    )
    {
        $this->orderRepository = $orderRepository;
        $this->cardRepository = $cardRepository;
    }


    public function addOrder()
    {
        $this->orderRepository->firstOrCreate($this->orderName);
        $this->message = 'Order added';
    }

    public function orderSelected()
    {
    }

    public function shipOrder()
    {
        $order = $this->orderRepository->findById($this->orderName);
        $order->ownedCards()->update(['order_id' => null]);
        $order->delete();

        $this->message = 'Order marked as shipped';
    }

    public function render()
    {
        $this->orders = $this->orderRepository->all();
        $cards = [];

        if($this->order) {
            $order = $this->orderRepository->findById($this->order);
            $cards = $this->cardRepository->getForOrder($order->id);
        }

        return view('livewire.orders', [
            'cards' => $cards
        ]);
    }
}
