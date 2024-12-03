<div class="p-10 text-stone-700">
    <p class="bg-green-300 text-stone-700">
        {{$message}}
    </p>
    <div class="py-10 flex flex-col xl:flex-row justify-center xl:justify-between">
        <select wire:model="set" wire:change="refresh" class="my-4 xl:mx-4 xl:my-0">
            <option value="">All sets</option>
            @foreach($sets as $s)
                <option value="{{$s->name}}">
                    {{$s->code}} : {{$s->name}}
                </option>
            @endforeach
        </select>
    </div>
    <div class="flex-col justify-between">
        <table>
            <thead>
            <tr>
                <th>Ygo Id</th>
                <th>Card</th>
                <th>Rarity</th>
                <th>Code</th>
                <th>Total</th>
                <th>Collectable</th>
                <th>Tradable</th>
                <th>Code</th>
            </tr>
            </thead>
            <tbody>
            @foreach($cardInstances ?? [] as $index => $ci)
                <tr class="py-2" wire:key="cardInstances.{{ $ci['id'] }}">
                    <td class="px-2">{{$ci['ygo_id']}}</td>
                    <td class="px-2">{{$ci['card_name']}}</td>
                    <td class="px-2">{{$ci['rarity']}}</td>
                    <td class="px-2">{{$ci['card_set_code']}}</td>
                    <td class="px-2 {{($ci['collectable'] == 0 && $ci['tradable'] == 0) ? '' : ($ci['valid'] ? 'text-green-600' : 'text-red-700')}}">{{$ci['total']}}</td>
                    <td class="px-2"><input type="number" wire:model="cardInstances.{{$index}}.collectable" wire:blur="revalidate"></td>
                    <td class="px-2"><input type="number" wire:model="cardInstances.{{$index}}.tradable"wire:blur="revalidate"></td>
                    <td class="px-2">{{$ci['card_set_code']}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <button class="dark:bg-green-800 hover:bg-green-700 text-white font-bold py-1 px-4 rounded" wire:click="autofill">Autofill</button>
        <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="save">Save</button>
        <p>Total Collectable: {{$totalCollectable}}</p>
        <p>Total Tradable: {{$totalTradable}}</p>
    </div>
</div>
