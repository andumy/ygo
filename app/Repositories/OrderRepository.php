<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Support\Collection;

class OrderRepository
{
    public function all(): Collection
    {
        return Order::all();
    }

    public function firstOrCreate(string $orderName): Order
    {
        return Order::firstOrCreate([
            'name' => $orderName,
        ]);
    }

    public function delete(int $id): void{
        Order::where('id',$id)->delete();
    }
}
