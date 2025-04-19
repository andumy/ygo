<div class="w-screen p-10 text-stone-700">
    <div class="pb-10">
        <h1 class="text-2xl font-bold">
            YU-GI-OH! Sets and instances
        </h1>
    </div>
    <p class="bg-green-300 text-stone-700">
        {{$message}}
    </p>
    <h2 class="text-ms font-bold">
        Register a new set
    </h2>
    <div class="py-10 flex justify-between">
        <div class="flex">
            <input
                class="appearance-none border rounded text-black"
                type="text"
                wire:model="set_name"
                placeholder="Set Name"
            >
            <input
                class="appearance-none border rounded text-black"
                type="text"
                wire:model="set_code"
                placeholder="Set Code"
            >
            <input
                class="appearance-none border rounded text-black"
                type="number"
                wire:model="set_amount"
                placeholder="Set Total Cards"
            >
            <input
                class="appearance-none border rounded text-black"
                type="date"
                wire:model="set_date"
                placeholder="Set Release Date"
            >
            <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="addSet">
                Add Set
            </button>
        </div>
    </div>
    <h2 class="text-ms font-bold">
        Register a new instance
    </h2>
    <div class="py-10 flex justify-between">
        <div class="flex">
            <input
                class="appearance-none border rounded text-black"
                type="text"
                wire:model="card_id"
                placeholder="Card Id"
            >
            <select
                class="appearance-none border rounded text-black"
                type="text"
                wire:model="set_id"
                placeholder="Set Code"
            >
                <option value="">Select a set</option>
                @foreach($sets as $s)
                    <option value="{{$s->id}}">{{$s->code}} : {{$s->name}}</option>
                @endforeach
            </select>
            <input
                class="appearance-none border rounded text-black"
                type="text"
                wire:model="card_set_code"
                placeholder="Card Set Code"
            >
            <select
                class="appearance-none border rounded text-black"
                type="text"
                wire:model="rarity"
                placeholder="Rarity"
            >
                <option value="">Select a rarity</option>
                @foreach($rarities as $code => $rarity)
                    <option value="{{$code}}">{{$rarity}} {{$code}}</option>
                @endforeach
            </select>
            <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="addInstance">
                Add Instance
            </button>
        </div>
    </div>
    @if($confirm)
        <div class="py-10 flex justify-between">
            <div class="flex flex-col">
                <div>
                    <p>
                        Add <b>{{$card->name}}</b> to <b>{{$set->name}}</b> with set code <b>{{$card_set_code}}</b> ?
                    </p>
                    <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="confirmSave">
                        Confirm
                    </button>
                </div>
                <img class="w-60 pt-10" src="{{asset('storage/'. $card->ygoId . '.jpg')}}">
            </div>
        </div>
    @endif
</div>
