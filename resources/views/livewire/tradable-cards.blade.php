<div class="p-10 text-stone-700">
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
    <div class="flex justify-between">
        <form wire:submit.prevent="save">
            <table>
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Card</th>
                    <th>Total</th>
                    <th>Collectable</th>
                    <th>Tradable</th>
                </tr>
                </thead>
                <tbody>
                @foreach($cardInstances ?? [] as $index => $ci)
                    <tr class="py-2" wire:key="cardInstances.{{ $ci['id'] }}">
                        <td class="px-2">{{$ci['card_set_code']}}</td>
                        <td class="px-2">{{$ci['card_name']}}</td>
                        <td class="px-2">{{$ci['total']}}</td>
                        <td class="px-2"><input type="number" wire:model="cardInstances.{{$index}}.collectable"></td>
                        <td class="px-2"><input type="number" wire:model="cardInstances.{{$index}}.tradable"></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <button type="submit" class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded">Save</button>
        </form>
    </div>
</div>
