<?php

namespace App\Repositories;

use App\Models\OwnedCard;

class OwnedCardRepository
{
    public function create(array $data): OwnedCard{
        return OwnedCard::create($data);
    }

    public function countAmountOfCards(): int {
        return OwnedCard::sum('amount')+OwnedCard::sum('order_amount');
    }

    public function firstOrCreate(int $cardInstanceId, int $amount, int $orderAmount, ?int $orderId): OwnedCard {
        return OwnedCard::firstOrCreate([
            'card_instance_id' => $cardInstanceId,
            'amount' => $amount,
            'order_amount' => $orderAmount,
            'order_id' => $orderId
        ]);
    }

}
