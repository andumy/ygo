<?php

namespace App\Livewire;

use App\Repositories\OrderRepository;
use Illuminate\Support\Collection;
use Livewire\Component;

class Orders extends Component
{
    private OrderRepository $orderRepository;

    public string $message = '';
    public string $orderName;
    public ?int $order = null;
    public Collection $orders;

    public function boot(
        OrderRepository $orderRepository
    )
    {
        $this->orderRepository = $orderRepository;
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
        return view('livewire.orders', [
            'cards' => $this->order ?
                $this->orderRepository->findById($this->order)->ownedCards()->paginate(50) : []
        ]);
    }
}
