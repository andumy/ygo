@php
    use App\Models\Order;
    use App\Models\OwnedCard;
    /**
    * @var OwnedCard $oc
    * @var Order $o
    * */
@endphp
<div class="w-screen p-10 text-stone-700">
    <div class="pb-10">
        <h1 class="text-2xl font-bold">
            YU-GI-OH! Orders
        </h1>
    </div>
    <p class="bg-green-300 text-stone-700">
        {{$message}}
    </p>
    <h2 class="text-ms font-bold">
        Register a new order
    </h2>
    <div class="py-10 flex justify-between">
        <div class="flex">
            <input
                class="appearance-none border rounded text-black"
                type="text"
                wire:model="orderName"
                placeholder="Order name"
            >
            <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded"
                    wire:click="addOrder">
                Add Order
            </button>
        </div>
    </div>

    <h2 class="text-ms font-bold">
        Mark order as shipped
    </h2>
    <div class="py-10 flex justify-between">
        <div class="flex">
            <select wire:model="orderId" wire:change="refresh" class="px-4">
                <option value="" selected>Select Order</option>
                @foreach($orders as $o)
                    <option value="{{$o->id}}">{{$o->name}}</option>
                @endforeach
            </select>
            <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded"
                    wire:click="shipOrder">
                Mark order as shipped
            </button>
        </div>
    </div>
    @if($orderedCards)
        <table>
            <thead>
            <tr>
                <th>OC ID</th>
                <th>Card Name</th>
                <th>Card Code</th>
                <th>Language</th>
                <th>Condition</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($orderedCards as $oc)
                <tr class="py-2">
                    <td>{{$oc->cardInstance->ownedCards->where('lang', $oc->lang)->where('cond', $oc->cond)->where('order_id', $orderId)->pluck('id')->reduce(fn($carry, $id) => "$carry $id",'')}}</td>
                    <td class="
                    {{
                            $oc->cardInstance->isOwnedForLang($oc->lang) ? 'text-orange-700' : (
                                $oc->cardInstance->isOwned ? 'text-amber-400' : (
                                    $oc->cardInstance->card->isOwned ? 'text-teal-400' : ''
                                )
                            )
                    }}
                    ">{{$oc->cardInstance->card->name}}</td>
                    <td>{{$oc->cardInstance->card_set_code}}</td>
                    <td><img src="{{$oc->lang->getFlag()}}" alt="{{$oc->lang->value}}" class="h-[14px]"></td>
                    <td>{!! $oc->cond->getShortHand() !!}</td>
                    <td>{{$oc->amount}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>
