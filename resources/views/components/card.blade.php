<div class="flex flex-col text-stone-400 relative" data-hide-tooltip="true">
    <img
        @if(!$card->isOrdered && !$card->isOwned)
            class="grayscale opacity-80"
        @endif

        @if($card->isOrdered && !$card->isOwned)
            class="sepia opacity-80"
        @endif

        @if($card->isOwned)
            @if($setCode && $setCode !== "" && !$card->cardInstances->contains(fn($ci) => str_contains($ci->card_set_code,$setCode) && $ci->ownedCard?->amount > 0))
                class="opacity-50"
           @endif
        @endif

        src="
        @if (file_exists(public_path('storage/'. $card->ygo_id . '.jpg')))
            {{asset('storage/'. $card->ygo_id . '.jpg')}}
        @elseif (file_exists(public_path('storage/'. $card->ygo_id . '.png')))
            {{asset('storage/'. $card->ygo_id . '.png')}}
        @endif
        ">
        @if($card->card_id)
            <div class="absolute bg-red-600 w-[20px] h-[20px] rounded-full top-[-10px] left-[-10px]"></div>
        @endif
    <p id="id-{{$card->id}}" class="text-xs text-center font-bold text-stone-500 cursor-pointer hover:text-stone-400" onclick="copyName('id-{{$card->id}}')">{{ $card->ygo_id }}</p>
    <h2 id="card-{{$card->id}}" class="text-center font-bold text-stone-800 cursor-pointer hover:text-stone-500 pb-2" onclick="copyName('card-{{$card->id}}')">{{ $card->name }}</h2>
    @foreach($card->cardInstances->filter(fn($ci) => !($setCode && $setCode !== "" && !str_contains($ci->card_set_code,$setCode))) as $instance)
        <div class="flex flex-row justify-center relative has-tooltip cursor-pointer
        @if($instance->ownedCard || $instance->orderedCards->count() > 0)
             font-bold
             @if($instance->ownedCard)
                 text-cyan-500
             @else
                text-yellow-500
             @endif
        @endif">
            @if($instance->orderedCards->count() > 0)
                <p class="text-xs text-yellow-500">({{ $instance->orderedCards->reduce(fn($c, $oc) => $c + $oc->amount,0) }})</p>
            @endif

            @if($instance->ownedCard)
                <p class="text-xs text-cyan-500"> {{$instance->ownedCard->amount}} x&nbsp;</p>
            @endif

            <p data-tooltip-target="{{$instance->id}}"  class="text-xs m-0"> {{$instance->card_set_code}} {{$instance->rarity}} : {{$instance->price?->price ?? '-'}} €</p>

            <div id="tooltip-{{$instance->id}}" class="js-tooltip absolute hidden bg-white rounded-xl z-50 flex p-2 top-5 right-0 text-stone-800 font-normal">
                <div class="flex flex-col">
                    <div class="flex align-center">
                        <b>{{$instance->card_set_code}}</b>
                        <input
                            class="appearance-none border rounded text-black w-16 ps-2"
                            type="number"
                            id="prices.{{$instance->id}}"
                            wire:model="prices.{{$instance->id}}"
                            wire:change="updatePrice({{$instance->id}})"
                        >
                        <b>€</b>
                    </div>
                    <div class="p-2">
                        <label for="ownedCards.{{$instance->id}}">Owned: </label>
                        <input
                            class="appearance-none border rounded text-black"
                            type="number"
                            id="ownedCards.{{$instance->id}}"
                            wire:model="ownedCards.{{$instance->id}}"
                        >
                        <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="updateOwn({{$instance->id}})">
                            Update Own
                        </button>
                    </div>
                    <div class="p-2">
                        @if($instance->orderedCards->count() > 0)
                            <p>Ordered: </p>
                        @endif
                        @foreach($instance->orderedCards as $orderedCard)
                            <label for="orderedCards.{{$instance->id}}.{{$orderedCard->id}}">
                                {{$orderedCard->order->name}}:
                            </label>
                            <input
                                class="appearance-none border rounded text-black"
                                type="number"
                                wire:model="orderedCards.{{$instance->id}}.{{$orderedCard->order_id}}"
                                id="orderedCards.{{$instance->id}}.{{$orderedCard->id}}"
                            >
                        @endforeach
                        @if($instance->orderedCards->count() > 0)
                            <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="updateOrders({{$instance->id}})">
                                Update Orders
                            </button>
                        @endif
                    </div>
                    <div class="p-2">
                        <label for="orderedCards.{{$instance->id}}.0">
                            New Order:
                        </label>
                        <input
                            class="appearance-none border rounded text-black"
                            type="text"
                            wire:model="orderedCards.{{$instance->id}}.0"
                        >
                        <select wire:model="orderId.{{$instance->id}}" class="px-4 mx-4">
                            <option value="" selected>Select Order</option>
                            @foreach($orders as $o)
                                <option value="{{$o->id}}">{{$o->name}}</option>
                            @endforeach
                        </select>
                        <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="addOrder({{$instance->id}})">
                            Add Order
                        </button>
                    </div>
                </div>
            </div>
        </div>


    @endforeach
</div>

<script>
    function copyName(selector) {
        const name = document.getElementById(selector).innerText;
        navigator.clipboard.writeText(name);
    }

    function registerTooltips() {
        document.body.addEventListener('click', function (event) {
            const tooltip = event.target.getAttribute('data-tooltip-target');
            const hideTooltip = event.target.getAttribute('data-hide-tooltip');

            if(hideTooltip){
                hideAllTooltips();
            }

            if(!tooltip){
                return;
            }
            hideAllTooltips();
            document.getElementById('tooltip-'+tooltip)?.classList.remove('hidden');
        });
    }

    function hideAllTooltips() {
        document.querySelectorAll('.js-tooltip').forEach((element) => {
            element.classList.add('hidden');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        registerTooltips();
    });
</script>
