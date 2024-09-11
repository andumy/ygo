<?php

namespace App\Http\Controllers;

use App\Enums\AddCardStatuses;
use App\Models\CardInstance;
use App\Repositories\CardRepository;
use App\Repositories\OrderRepository;
use App\Services\CardService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use function response;
use function str_contains;

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
        CardService $cardService
    ) {
        $cardName = $request->get('card');
        $orderName = $request->get('order');
        $code = $request->get('code');
        $rarity = $request->get('rarity');

        if($rarity === 'undefined'){
            $rarity = null;
        }

        $order = $orderRepository->firstOrCreate($orderName);
        $card = $cardRepository->findByName($cardName);
        /** @var Collection<CardInstance> $cardInstance */
        $cardInstances = $card->cardInstances->filter(function (CardInstance $instance) use ($code, $rarity) {
            return str_contains($instance->card_set_code , $code) &&
                ($rarity === null || $instance->rarity_verbose === $rarity);
        });


        if($cardInstances->count() > 1){
            return response()->json(['message' => 'Multiple instances found']);
        }

        if($cardInstances->isEmpty()){
            return response()->json(['message' => 'Not Found']);
        }

        $response = $cardService->updateCardStock(
            code: $cardInstances->first()->card_set_code,
            rarity: $rarity,
            orderId: $order->id
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
