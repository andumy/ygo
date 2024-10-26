<?php

namespace App\Services;

use App\Dtos\AddCardResponse;
use App\Enums\AddCardStatuses;
use App\Models\CardInstance;
use App\Repositories\CardInstanceRepository;
use App\Repositories\OrderedCardRepository;
use App\Repositories\OwnedCardRepository;
use Illuminate\Support\Collection;

class CardService
{
    public function __construct(
        private readonly OwnedCardRepository    $ownedCardRepository,
        private readonly OrderedCardRepository    $orderedCardRepository,
        private readonly CardInstanceRepository $cardInstanceRepository,
    )
    {
    }

    public function updateCardStock(
        string $code,
        ?string $rarity = null,
        int $orderId = null,
        int $orderAmount = 1,
        int $ownAmount = 1,
        bool $shouldIncrease = false
    ): AddCardResponse
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
                cardName: $cardInstances->first()->card->name,
                cardInstance: $cardInstances->first()
            );
        }
        $cardInstance = $cardInstances->first();

        if($cardInstances->count() > 1) {
            $cardInstance = $cardInstances->where('rarity_verbose', $rarity)->first();
        }

        return $this->updateCardStockFromInstance(
            $cardInstance,
            $orderId,
            $orderAmount,
            $ownAmount,
            $shouldIncrease
        );
    }

    public function updateCardStockFromInstance(
        CardInstance $cardInstance,
        int $orderId = null,
        int $orderAmount = 1,
        int $ownAmount = 1,
        bool $shouldIncrease = false
    ): AddCardResponse
    {
        if($orderId){
            return $this->handleOrderedCards($cardInstance, $orderId, $orderAmount, $shouldIncrease);
        }
        return $this->handleOwnedCards($cardInstance, $ownAmount, $shouldIncrease);
    }

    private function handleOwnedCards(
        CardInstance $cardInstance,
        int $ownAmount,
        bool $shouldIncrease
    ): AddCardResponse
    {
        //delete owned
        if($ownAmount === 0) {
            $this->ownedCardRepository->delete($cardInstance->id);
            return new AddCardResponse(
                status: AddCardStatuses::DELETE,
                cardName: $cardInstance->card->name,
                cardInstance: $cardInstance
            );
        }

        //update owned
        if($cardInstance->ownedCard){
            $this->ownedCardRepository
                ->updateAmount(
                    $cardInstance->ownedCard,
                    $shouldIncrease ? $cardInstance->ownedCard->amount + $ownAmount : $ownAmount
                );
            return new AddCardResponse(
                status: AddCardStatuses::INCREMENT,
                cardName: $cardInstance->card->name,
                cardInstance: $cardInstance
            );
        }

        //create owned
        $this->ownedCardRepository->firstOrCreate(
            $cardInstance->id,
            $ownAmount,
        );

        return new AddCardResponse(
            status: AddCardStatuses::NEW_CARD,
            cardName: $cardInstance->card->name,
            cardInstance: $cardInstance
        );
    }

    private function handleOrderedCards(
        CardInstance $cardInstance,
        int $orderId,
        int $orderAmount,
        bool $shouldIncrease
    ): AddCardResponse
    {
        //delete order
        if($orderAmount === 0) {
            $this->orderedCardRepository->delete($cardInstance->id, $orderId);
            return new AddCardResponse(
                status: AddCardStatuses::DELETE,
                cardName: $cardInstance->card->name,
                cardInstance: $cardInstance
            );
        }

        //update order
        if($orderedCard = $this->orderedCardRepository->findByInstanceAndOrder($cardInstance->id, $orderId)){
            $this->orderedCardRepository->updateAmount(
                $orderedCard,
                $shouldIncrease ? $orderedCard->amount + $orderAmount : $orderAmount
            );
            return new AddCardResponse(
                status: AddCardStatuses::INCREMENT,
                cardName: $cardInstance->card->name,
                cardInstance: $cardInstance
            );
        }

        //create order
        $this->orderedCardRepository->firstOrCreate($cardInstance->id, $orderAmount, $orderId);
        return new AddCardResponse(
            status: AddCardStatuses::NEW_CARD,
            cardName: $cardInstance->card->name,
            cardInstance: $cardInstance
        );
    }
}
