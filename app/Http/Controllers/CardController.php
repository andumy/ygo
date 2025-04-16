<?php

namespace App\Http\Controllers;

use App\Enums\AddCardStatuses;
use App\Enums\Condition;
use App\Enums\Lang;
use App\Enums\Rarities;
use App\Models\Card;
use App\Models\CardInstance;
use App\Repositories\CardRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OwnedCardRepository;
use App\Services\CardService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use function response;
use function str_contains;
use function var_dump;

class CardController extends Controller
{

    public function cardInfo(Request $request, CardRepository $cardRepository)
    {
        $cardName = $request->get('card');

        $card = $cardRepository->findByName($cardName);

        if(!$card){
            return response()->json(['message' => 'Card not found']);
        }

        if($card->isOwned || $card->isOrdered){
            return response()->json(['message' => 'Card owned']);
        }

        return response()->json(['message' => 'Card needed']);
    }

    public function orderCard(
        Request $request,
        CardRepository $cardRepository,
        OrderRepository $orderRepository,
        CardService $cardService,
        OwnedCardRepository $ownedCardRepository
    ) {
        $cardName = $request->get('card');
        $code = $request->get('code');
        $orderName = $request->get('order');
        $version = $request->get('version');
        $rarity = Rarities::tryFrom($request->get('rarity'));
        $isFirstEdition = (bool)$request->get('is_first_edition');
        $lang = Lang::from($request->get('lang') ?? 'EN');
        $condition = Condition::from($request->get('condition') ?? 'NM');



        $batch = $ownedCardRepository->fetchNextBatch();

        $order = $orderRepository->firstOrCreate($orderName);
        $card = $cardRepository->findByName($cardName);
        /** @var Collection<CardInstance> $cardInstance */
        $cardInstances = $card->cardInstances->filter(function (CardInstance $instance) use ($code, $rarity, $version) {
            $cond = false;
            switch ($version){
                case 0:
                    $cond = str_contains($instance->card_set_code , "$code-EN");
                    break;
                case 1:
                    $cond = !str_contains($instance->card_set_code , "$code-EN") &&
                        str_contains($instance->card_set_code , "$code-E");
                    break;
                case 2:
                    $cond = !str_contains($instance->card_set_code , "$code-EN") &&
                        !str_contains($instance->card_set_code , "$code-E") &&
                        str_contains($instance->card_set_code , "$code-");
                    break;
            }

            return $cond && ($rarity === null || $instance->rarity_verbose->value === $rarity->value);
        });


        if($cardInstances->count() > 1){
            return response()->json(['message' => 'Multiple instances found', 'instances' => $cardInstances->pluck('id')->toArray()]);
        }

        if($cardInstances->isEmpty()){
            return response()->json(['message' => 'Not Found']);
        }

        $response = $cardService->updateCardStock(
            code: $cardInstances->first()->card_set_code,
            batch: $batch,
            option: $cardInstances->first(),
            orderId: $order->id,
            amount: 1,
            shouldIncrease: true,
            lang: $lang,
            condition: $condition,
            isFirstEdition: $isFirstEdition
        );

        if(
            $response->status === AddCardStatuses::NEW_CARD ||
            $response->status === AddCardStatuses::INCREMENT
        ) {
            return response()->json(['message' => 'Added']);
        }

        return response()->json(['message' => 'Manual addition required']);
    }
}
