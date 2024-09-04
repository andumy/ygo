<?php

namespace App\Services;

use App\Dtos\AddCardResponse;
use App\Enums\AddCardStatuses;
use App\Models\CardInstance;
use App\Repositories\CardInstanceRepository;
use App\Repositories\OwnedCardRepository;
use Illuminate\Support\Collection;

class CardService
{
    public function __construct(
        private readonly OwnedCardRepository    $ownedCardRepository,
        private readonly CardInstanceRepository $cardInstanceRepository,
    )
    {
    }

    public function addCard(string $code, ?string $rarity = null): AddCardResponse
    {
        /** @var Collection<CardInstance> $cardInstances */
        $cardInstances = $this->cardInstanceRepository->findBySetCode($code);

        if($cardInstances->isEmpty()){
            return new AddCardResponse(
                status: AddCardStatuses::NOT_FOUND,
                cardName: $code
            );
        }

        if($cardInstances->count() > 1 && !$rarity){
            return new AddCardResponse(
                status: AddCardStatuses::MULTIPLE_OPTIONS,
                rarities: $cardInstances->pluck('rarity_verbose')->toArray(),
                cardName: $cardInstances->first()->card->name
            );
        }
        $cardInstance = $cardInstances->first();

        if($cardInstances->count() > 1) {
            $cardInstance = $cardInstances->where('rarity_verbose', $rarity)->first();
        }

        $ownCard = $cardInstance->ownedCard;

        if($ownCard) {
            $ownCard->amount += 1;
            $ownCard->save();
            return new AddCardResponse(
                status: AddCardStatuses::INCREMENT,
                cardName: $cardInstance->card->name
            );
        }

         $this->ownedCardRepository->create([
            'card_instance_id' => $cardInstance->id,
            'amount' => 1,
        ]);
        return new AddCardResponse(
            status: AddCardStatuses::NEW_CARD,
            cardName: $cardInstance->card->name
        );
    }
}
