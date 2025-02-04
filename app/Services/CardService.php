<?php

namespace App\Services;

use App\Dtos\AddCardResponse;
use App\Enums\AddCardStatuses;
use App\Enums\Condition;
use App\Enums\Lang;
use App\Models\CardInstance;
use App\Repositories\CardInstanceRepository;
use App\Repositories\OwnedCardRepository;
use Illuminate\Support\Collection;
use function collect;

class CardService
{
    public function __construct(
        private readonly OwnedCardRepository    $ownedCardRepository,
        private readonly CardInstanceRepository $cardInstanceRepository,
    )
    {
    }

    public function updateCardStock(
        string $code,
        int $batch,
        ?CardInstance $option = null,
        int $orderId = null,
        ?int $amount = null,
        bool $shouldIncrease = false,
        Lang $lang = Lang::ENGLISH,
        Condition $condition = Condition::NEAR_MINT
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
            cardInstance: $cardInstance,
            batch: $batch,
            shouldIncrease: $shouldIncrease,
            lang: $lang,
            condition: $condition,
            orderId: $orderId,
            amount: $amount,
        );
    }

    public function updateCardStockFromInstance(
        CardInstance $cardInstance,
        int $batch,
        bool $shouldIncrease = false,
        Lang $lang = Lang::ENGLISH,
        Condition $condition = Condition::NEAR_MINT,
        ?int $orderId = null,
        ?int $amount = null,
    ): AddCardResponse
    {
        $ownedCards = $this->ownedCardRepository->cardsForUpdate(
            cardInstanceId: $cardInstance->id,
            lang: $lang,
            condition: $condition,
            orderId: $orderId
        );
        $ownChangeAmount = $shouldIncrease ? $amount : $amount - $ownedCards->count();

        if($ownChangeAmount === 0){
            return new AddCardResponse(
                status: AddCardStatuses::NO_CHANGE,
                options: collect([$cardInstance]),
            );
        }

        if($ownChangeAmount > 0){
            for ($i = 0; $i < $ownChangeAmount; $i++) {
                $this->ownedCardRepository->create(
                    cardInstanceId: $cardInstance->id,
                    batch: $batch,
                    lang: $lang,
                    condition: $condition,
                    orderId: $orderId,
                );
            }

            if($ownedCards->count() === 0) {
                return new AddCardResponse(
                    status: AddCardStatuses::NEW_CARD,
                    options: collect([$cardInstance])
                );
            }

            return new AddCardResponse(
                status: AddCardStatuses::INCREMENT,
                options: collect([$cardInstance])
            );

        }

        $ownedCards->take($ownChangeAmount * -1)->each(fn($ownedCard) => $ownedCard->delete());

        return new AddCardResponse(
            status: AddCardStatuses::DELETE,
            options: collect([$cardInstance])
        );
    }


//    private function handleOrderedCards(
//        CardInstance $cardInstance,
//        int $orderId,
//        int $orderAmount,
//        bool $shouldIncrease,
//        Lang $lang
//    ): AddCardResponse
//    {
//        //delete order
//        if($orderAmount === 0) {
//            $this->orderedCardRepository->delete($cardInstance->id, $orderId);
//            return new AddCardResponse(
//                status: AddCardStatuses::DELETE,
//                options: collect([$cardInstance])
//            );
//        }
//
//        //update order
//        if($orderedCard = $this->orderedCardRepository->findByInstanceOrderAndLang($cardInstance->id, $orderId, $lang)){
//            $this->orderedCardRepository->updateAmount(
//                $orderedCard,
//                $shouldIncrease ? $orderedCard->amount + $orderAmount : $orderAmount
//            );
//            return new AddCardResponse(
//                status: AddCardStatuses::INCREMENT,
//                options: collect([$cardInstance])
//            );
//        }
//
//        //create order
//        $this->orderedCardRepository->firstOrCreate($cardInstance->id, $orderAmount, $orderId, $lang);
//        return new AddCardResponse(
//            status: AddCardStatuses::NEW_CARD,
//            options: collect([$cardInstance])
//        );
//    }
}
