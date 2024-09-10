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

    public function findById(int $id): ?Order
    {
        return Order::find($id);
    }

    public function findByName(string $name): ?Order
    {
        return Order::where('name',$name)->first();
    }
}
