<div class="flex flex-col text-stone-400">
    <img
        @if($card->isOrdered)
            class="sepia opacity-80"
        @elseif(!$card->isOwned)
            class="grayscale opacity-80"
        @endif
        src="{{asset('storage/'. $card->ygo_id . '.jpg')}}">
    <h2 id="card-{{$card->id}}" class="text-center font-bold text-stone-800 cursor-pointer hover:text-stone-500" onclick="copyName({{$card->id}})">{{ $card->name }}</h2>
    @foreach($card->cardInstances as $instance)
        <div class="flex flex-row justify-center relative has-tooltip
            @if($instance->ownedCard)
                 font-bold
                 @if($instance->ownedCard->order_id !== null)
                     text-yellow-500
                 @else
                    text-cyan-500
                 @endif
            @endif">
            @if($instance->ownedCard)
                @if($instance->ownedCard->order_amount > 0)
                    <p class="text-xs pe-1">({{$instance->ownedCard->order_amount}}) </p>
                @endif
                <p class="text-xs"> {{$instance->ownedCard->amount}}</p>
                <p class="text-xs px-1">x</p>

            @endif
            <div data-tooltip-target="{{$instance->id}}" class="flex cursor-pointer">
                <p class="text-xs pointer-events-none"> {{$instance->card_set_code}}</p>
                <p class="text-xs pointer-events-none"> {{$instance->rarity}}</p>
            </div>
            <div id="tooltip-{{$instance->id}}" class="js-tooltip absolute hidden bg-white rounded-xl z-50 flex p-2 top-5 right-0 text-stone-800 font-normal">
                <div class="flex flex-col">
                    <p>{{$instance->card_set_code}}</p>
                    <div class="p-2">
                        Owned: <input
                            class="appearance-none border rounded text-black"
                            type="text"
                            wire:model="ownedCards.{{$instance->id}}"
                            value="{{$instance->ownedCard?->amount ?? 0}}"
                        >
                    </div>
                    <div class="p-2">
                        Ordered: <input
                            class="appearance-none border rounded text-black"
                            type="text"
                            wire:model="orderedCards.{{$instance->id}}"
                            value="{{$instance->ownedCard?->order_amaount ?? 0}}"
                        >
                        <select wire:model="orderId.{{$instance->id}}" class="px-4 mx-4">
                            <option value="" selected>Select Order</option>
                            @foreach($orders as $o)
                                <option value="{{$o->id}}">{{$o->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="updateStock({{$instance->id}})">
                        Update
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>
