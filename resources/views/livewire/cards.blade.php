<div class="w-screen p-10 text-stone-700">
    <div class="pb-10">
        <h1 class="text-2xl font-bold">
            YU-GI-OH! Library
        </h1>
        <div class="flex pt-2 justify-between">
            <div class="flex">
                <h3 class="text-lg font-bold text-cyan-400">
                    {{$owned}} Owned
                </h3>
                <p class="px-5 text-lg">/</p>
                <h3 class="text-lg font-bold">
                    {{$total}} Total
                </h3>
                <h3 class="text-lg font-bold ps-10 text-stone-300">
                    ({{$percentage}} %)
                </h3>
            </div>
            <div>
                <h3 class="text-lg font-bold text-cyan-700">
                    {{$amountOfCards}} Physical Cards
                </h3>
            </div>
        </div>
    </div>
    <p class="bg-green-300 text-stone-700">
        {{$message}}
    </p>
    <div class="py-10 flex justify-between">
        <div class="flex">
            <input
                class="appearance-none border rounded text-black"
                type="text"
                wire:model="code"
                placeholder="Card Code"
            >
            <select wire:model="rarity">
                @foreach($rarities as $r)
                    <option value="{{$r}}">{{$r}}</option>
                @endforeach
            </select>
            <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="addCard">
                Add Card
            </button>
        </div>


        <div class="flex">
            <select wire:model="set" wire:change="fetchCards" class="mx-4">
                <option value="">All sets</option>
                @foreach($sets as $s)
                    <option value="{{$s->name}}">
                        {{$s->name}}
                        ({{$fillBySet[$s->name]['owned']}} / {{$fillBySet[$s->name]['total']}})
                    </option>
                @endforeach
            </select>
            <input
                class="appearance-none border rounded text-black"
                type="text"
                wire:model="search"
                wire:keyup="fetchCards"
                placeholder="Search"
            >
        </div>
    </div>
    {{$cards->links()}}
    <div class="grid gap-6 grid-cols-10 grid-rows-1 py-5">
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
