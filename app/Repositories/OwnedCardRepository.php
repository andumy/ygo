<?php

namespace App\Repositories;

use App\Models\OwnedCard;

class OwnedCardRepository
{
    public function create(array $data): OwnedCard{
        return OwnedCard::create($data);
    }

    public function countAmountOfCards(): int {
        return OwnedCard::sum('amount');
    }

    public function firstOrCreate(int $cardInstanceId, int $amount): OwnedCard {
        return OwnedCard::firstOrCreate([
            'card_instance_id' => $cardInstanceId,
            'amount' => $amount,
        ]);
    }

    public function updateAmount(OwnedCard $ownedCard, int $amount): void {
        $ownedCard->amount = $amount;
        $ownedCard->save();
    }

    public function delete(int $cardInstanceId): void {
        OwnedCard::where('card_instance_id', $cardInstanceId)
            ->delete();
    }

}
