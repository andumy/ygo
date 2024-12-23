<?php

namespace App\Services;

use App\Dtos\AddCardResponse;
use App\Enums\AddCardStatuses;
use App\Events\StockUpdateEvent;
use App\Models\CardInstance;
use App\Repositories\CardInstanceRepository;
use App\Repositories\OrderedCardRepository;
use App\Repositories\OwnedCardRepository;
use Illuminate\Support\Collection;
use function collect;

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
        ?CardInstance $option = null,
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
            );
        }

        if($cardInstances->count() > 1 && !$option){
            return new AddCardResponse(
                status: AddCardStatuses::MULTIPLE_OPTIONS,
                options: $cardInstances,
            );
        }


        if($cardInstances->count() > 1) {
            $cardInstance = $option;
        } else {
            $cardInstance = $cardInstances->first();
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
        bool $shouldIncrease,
        int $orderId = null,
        int $orderAmount = 1,
        int $ownAmount = 1,
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

        StockUpdateEvent::dispatch(
            $cardInstance->set,
            true
        );

        //delete owned
        if($ownAmount === 0) {
            $this->ownedCardRepository->delete($cardInstance->id);
            return new AddCardResponse(
                status: AddCardStatuses::DELETE,
                options: collect([$cardInstance])
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
                options: collect([$cardInstance])
            );
        }

        //create owned
        $this->ownedCardRepository->firstOrCreate(
            $cardInstance->id,
            $ownAmount,
        );

        return new AddCardResponse(
            status: AddCardStatuses::NEW_CARD,
            options: collect([$cardInstance])
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
                options: collect([$cardInstance])
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
                options: collect([$cardInstance])
            );
        }

        //create order
        $this->orderedCardRepository->firstOrCreate($cardInstance->id, $orderAmount, $orderId);
        return new AddCardResponse(
            status: AddCardStatuses::NEW_CARD,
            options: collect([$cardInstance])
        );
    }
}
