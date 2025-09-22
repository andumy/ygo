<div class="w-screen p-10 text-stone-700">
    <div class="pb-10">
        <h1 class="text-2xl font-bold">
            YU-GI-OH! Sync Set
        </h1>
    </div>
    <p class="bg-green-300 text-stone-700">
        {{$message}}
    </p>
    <h2 class="text-ms font-bold">
        Sync a set
    </h2>
    <div class="py-10 flex flex-col justify-between">
        <div class="flex w-full">
            <select
                class="appearance-none border rounded text-black"
                wire:model.live="setId"
            >
                @foreach($sets as $s)
                    <option value="{{$s->id}}">{{$s->code}} : {{$s->name}}</option>
                @endforeach
            </select>
            <textarea
                class="appearance-none border rounded text-black w-full"
                wire:model.live="data"
                rows="10"
                placeholder="Data"
            ></textarea>
            <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="fetch">
                Sync Set
            </button>
        </div>
        @if($passCodes)
            <div class="flex flex-col w-full">
                @foreach($passCodes as $card => $passCode)
                    <label>
                        {{$card}}
                        <input type="text" wire:model.live="passCodes.{{$card}}">
                    </label>
                @endforeach
                <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="injectData">
                    Sync Cards
                </button>
            </div>
        @endif
    </div>
</div>
