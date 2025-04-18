<?php

namespace App\Services;

use App\Dtos\AddCardResponse;
use App\Enums\AddCardStatuses;
use App\Enums\Condition;
use App\Enums\Lang;
use App\Models\Variant;
use App\Repositories\OwnedCardRepository;
use App\Repositories\VariantRepository;
use Illuminate\Support\Collection;
use function collect;

class CardService
{
    public function __construct(
        private readonly OwnedCardRepository    $ownedCardRepository,
        private readonly VariantRepository $variantRepository,
    )
    {
    }

    public function updateCardStock(
        string $code,
        int $batch,
        ?Variant $variant = null,
        int $orderId = null,
        ?int $amount = null,
        bool $shouldIncrease = false,
        Lang $lang = Lang::ENGLISH,
        Condition $condition = Condition::NEAR_MINT,
        ?bool $isFirstEdition = null
    ): AddCardResponse
    {
        /** @var Collection<Variant> $variants */
        $variants = $this->variantRepository->getBySetCode($code);

        if($variants->isEmpty()){
            return new AddCardResponse(
                status: AddCardStatuses::NOT_FOUND,
            );
        }

        if($variants->count() > 1 && !$variant){
            return new AddCardResponse(
                status: AddCardStatuses::MULTIPLE_OPTIONS,
                options: $variants,
            );
        }


        if($variants->count() === 1) {
            $variant = $variants->first();
        }

        return $this->updateCardStockFromInstance(
            variant: $variant,
            batch: $batch,
            shouldIncrease: $shouldIncrease,
            lang: $lang,
            condition: $condition,
            orderId: $orderId,
            amount: $amount,
            isFirstEdition: $isFirstEdition
        );
    }

    public function updateCardStockFromInstance(
        Variant $variant,
        int $batch,
        bool $shouldIncrease = false,
        Lang $lang = Lang::ENGLISH,
        Condition $condition = Condition::NEAR_MINT,
        ?int $orderId = null,
        ?int $amount = null,
        ?bool $isFirstEdition = null
    ): AddCardResponse
    {
        $ownedCards = $this->ownedCardRepository->cardsForUpdate(
            variantId: $variant->id,
            lang: $lang,
            condition: $condition,
            orderId: $orderId,
            isFirstEdition: $isFirstEdition
        );
        $ownChangeAmount = $shouldIncrease ? $amount : $amount - $ownedCards->count();

        if($ownChangeAmount === 0){
            return new AddCardResponse(
                status: AddCardStatuses::NO_CHANGE,
                options: collect([$variant]),
            );
        }

        if($ownChangeAmount > 0){
            for ($i = 0; $i < $ownChangeAmount; $i++) {
                $this->ownedCardRepository->create(
                    variantId: $variant->id,
                    batch: $batch,
                    lang: $lang,
                    condition: $condition,
                    orderId: $orderId,
                    isFirstEdition: $isFirstEdition
                );
            }

            if($ownedCards->count() === 0) {
                return new AddCardResponse(
                    status: AddCardStatuses::NEW_CARD,
                    options: collect([$variant])
                );
            }

            return new AddCardResponse(
                status: AddCardStatuses::INCREMENT,
                options: collect([$variant])
            );

        }

        $ownedCards->take($ownChangeAmount * -1)->each(fn($ownedCard) => $ownedCard->delete());

        return new AddCardResponse(
            status: AddCardStatuses::DELETE,
            options: collect([$variant])
        );
    }
}
