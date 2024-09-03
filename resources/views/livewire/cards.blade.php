<div class="w-screen p-10 text-stone-700">
    <div class="pb-10 text-xl font-bold">
        <h1>
            YU-GI-OH! Library
        </h1>
    </div>
    <div class="">
        <p class="bg-green-300 text-white">
            {{$message}}
        </p>
    </div>
    <div class="py-10 flex justify-between">
        <div class="flex">
            <input
                class="appearance-none border rounded text-black"
                type="text"
                wire:model="code"
            >
            <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="addCard">
                Add Card
            </button>
        </div>

        <div class="flex">
            <input
                class="appearance-none border rounded text-black"
                type="text"
                wire:model="search"
                wire:keyup="fetchCards"
                placeholder="Search"
            >
        </div>

    </div>
    <div class="grid gap-6 grid-cols-10 grid-rows-1">
        @foreach($cards as $card)
            <div class="flex flex-col text-stone-400">
                <img @if(!$card->isOwned)
                         class="grayscale opacity-80"
                     @endif src="{{asset('storage/'. $card->ygo_id . '.jpg')}}">
                <h2 class="text-center font-bold text-stone-800">{{ $card->name }}</h2>
                @foreach($card->cardInstances as $instance)
                    <div class="flex flex-row justify-center @if($instance->ownedCard)
                         text-cyan-500 font-bold
                    @endif">
                        @if($instance->ownedCard)
                        <p class="text-xs">{{$instance->ownedCard->amount}}</p>
                        <p class="text-xs px-2">x</p>

                        @endif
                        <p class="text-xs"> {{$instance->card_set_code}}</p>
                        <p class="text-xs"> {{$instance->rarity}}</p>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
    {{$cards->links()}}
</div>
