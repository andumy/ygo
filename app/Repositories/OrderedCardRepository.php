<?php

namespace App\Repositories;

use App\Models\OrderedCard;
use Illuminate\Support\Collection;

class OrderedCardRepository
{

    public function firstOrCreate(int $cardInstanceId, int $amount, int $orderId): OrderedCard {
        return OrderedCard::firstOrCreate([
            'card_instance_id' => $cardInstanceId,
            'amount' => $amount,
            'order_id' => $orderId
        ]);
    }

    public function countAmountOfCards(): int {
        return OrderedCard::sum('amount');
    }

    public function delete(int $cardInstanceId, int $orderId): void {
        OrderedCard::where('card_instance_id', $cardInstanceId)
            ->where('order_id', $orderId)
            ->delete();
    }

    public function updateAmount(OrderedCard $orderedCard, int $amount): void {
        $orderedCard->amount = $amount;
        $orderedCard->save();
    }

    public function findByInstanceAndOrder(int $cardInstanceId, int $orderId): ?OrderedCard {
        return OrderedCard::where('card_instance_id', $cardInstanceId)
            ->where('order_id', $orderId)
            ->first();
    }

    /** @return Collection<OrderedCard> */
    public function getByOrderId(int $orderId): Collection
    {
        return OrderedCard::where('order_id',$orderId)->get();
    }
}
