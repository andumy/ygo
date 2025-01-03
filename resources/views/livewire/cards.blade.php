<div class="p-10 text-stone-700">
    <div class="pb-10">
        <h1 class="text-2xl font-bold">
            YU-GI-OH! Library
        </h1>
        <div class="flex pt-2 justify-between">
            <div class="flex-col">
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
                <div class="flex">
                    <h3 class="text-lg font-bold text-cyan-400">
                        {{$ownedInstances}} Owned Instances
                    </h3>
                    <p class="px-5 text-lg">/</p>
                    <h3 class="text-lg font-bold">
                        {{$totalInstances}} Total Instances
                    </h3>
                    <h3 class="text-lg font-bold ps-10 text-stone-300">
                        ({{$percentageInstances}} %)
                    </h3>
                </div>
            </div>
            <div class="flex flex-col">
                <h3 class="text-lg font-bold text-cyan-700">
                    {{$amountOfCards}} Physical Cards ({{$amountOfCards - $ownedInstances}} duplicates)
                </h3>
                <div class="pt-2">
                    <p class="text-lg font-bold text-cyan-700">Estimated value: {{$totalPrice}} €</p>
                </div>
            </div>
        </div>
    </div>
    <p class="bg-green-300 text-stone-700">
        {{$message}}
    </p>
    <div class="py-10 flex flex-col xl:flex-row justify-center xl:justify-between">
        <div class="flex">
            <input
                class="appearance-none border rounded text-black"
                type="text"
                wire:model="code"
                placeholder="Card Code"
            >
           @if(count($rarities) > 0)
                <select wire:model="rarity" class="px-4">
                    <option value="" selected disabled>Select Rarity</option>
                    @foreach($rarities as $r)
                        <option value="{{$r}}">{{$r}}</option>
                    @endforeach
                </select>
           @endif
            <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="addCard">
                Add Card
            </button>
        </div>
        <div class="flex flex-col xl:flex-row justify-center my-4 xl:my-0">
            <div class="flex">
                <select id="ownedFilter" name="ownedFilter" wire:model="ownedFilter" wire:change="refresh">
                    <option value="0">All</option>
                    <option value="1">Owned</option>
                    <option value="-1">Missing</option>
                </select>
            </div>
            <select wire:model="set" wire:change="refresh" class="my-4 xl:mx-4 xl:my-0">
                <option value="">All sets</option>
                @foreach($sets as $s)
                    <option value="{{$s->name}}">
                        {{$s->code}} : {{$s->name}}
{{--                        ({{$fillBySet[$s->name]['owned']}} / {{$fillBySet[$s->name]['total']}})--}}
                    </option>
                @endforeach
            </select>
            <input
                class="appearance-none border rounded text-black"
                type="text"
                wire:model="search"
                wire:keyup="refresh"
                placeholder="Search"
            >
        </div>
    </div>
    {{$cards->links()}}
    <div class="grid gap-3 grid-cols-1 md:grid-cols-3 lg:grid-cols-6 xl:grid-cols-8 2xl:grid-cols-9 grid-rows-1 py-5">
        @foreach($cards as $card)
            @include('components.card', ['card' => $card])
        @endforeach
    </div>
    {{$cards->links()}}
</div>
