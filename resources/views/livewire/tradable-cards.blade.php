@php use App\Enums\Condition;use App\Enums\Lang; @endphp
<div class="p-10 text-stone-700">
    <p class="bg-green-300 text-stone-700">
        {{$message}}
    </p>
    <div class="py-10 flex flex-col xl:flex-row justify-center xl:justify-between">
        <select wire:model="set" wire:change="refresh" class="my-4 xl:mx-4 xl:my-0">
            <option value="">All sets</option>
            @foreach($sets as $s)
                <option {{$this->set === $s->name ? 'selected' : ''}} value="{{$s->name}}">
                    {{$s->code}} : {{$s->name}}
                </option>
            @endforeach
        </select>
    </div>
    <div class="flex-col justify-between">
        <table>
            <thead>
            <tr>
                <th class="text-center px-4">Ygo Id</th>
                <th class="text-center px-4">Card</th>
                <th class="text-center px-4">Rarity</th>
                <th class="text-center px-4">Code</th>
                <th class="text-center px-4">Not Set</th>
                <th class="text-center px-4">Collectable</th>
                <th class="text-center px-4">Tradable</th>
                <th class="text-center px-4">Language</th>
                <th class="text-center px-4">Condition</th>
                <th class="text-center px-4">1st Edition</th>
                <th class="text-center px-4">Action</th>
            </tr>
            </thead>
            <tbody>
            @php $bgLight = true; $prevCard = null; @endphp
            @foreach($variants ?? [] as $variantId => $variantArray)
                @foreach($variantArray as $lang => $langArray)
                    @foreach($langArray as $cond => $condArray)
                        @foreach($condArray as $isFirstEd => $ownedCard)
                            @php
                                if($prevCard !== $ownedCard['card_set_code']){
                                    $bgLight = !$bgLight;
                                    $prevCard = $ownedCard['card_set_code'];
                                }
                            @endphp
                            <tr class="py-2 {{!$isMissing[$ownedCard['card_set_code']] ? 'bg-red-200' : ($bgLight ? 'bg-gray-100' : 'bg-gray-200')}} {{$ownedCard['not_set'] == 0 ? 'opacity-40' : ''}}"
                                wire:key="variants.{{$variantId}}.{{$lang}}.{{$cond}}.{{$isFirstEd}}">
                                <td class="px-2 text-center">{{$ownedCard['ygo_id']}}</td>
                                <td class="px-2 text-center">{{$ownedCard['card_name']}}</td>
                                <td class="px-2 text-center">{{$ownedCard['rarity']}}</td>
                                <td class="px-2 text-center">{{$ownedCard['card_set_code']}}</td>
                                <td class="px-2 text-center
                                    {{$ownedCard['not_set'] == 0 ? 'text-green-400' : 'text-white'}}
                                    {{$ownedCard['not_set'] == 0 ? '' : ($ownedCard['not_set'] < 0 ? 'bg-red-900' : 'bg-black')}}
                                ">{{ $ownedCard['not_set'] }}</td>
                                <td class="px-2 text-center"><input type="number" class="w-[100px]"
                                                                    wire:model="variants.{{$variantId}}.{{$lang}}.{{$cond}}.{{$isFirstEd}}.new_collectable"
                                                                    wire:blur="revalidate"></td>
                                <td class="px-2 text-center"><input type="number" class="w-[100px]"
                                                                    wire:model="variants.{{$variantId}}.{{$lang}}.{{$cond}}.{{$isFirstEd}}.new_tradable"
                                                                    wire:blur="revalidate"></td>
                                <td class="px-2 text-center"><img src="{{Lang::from($lang)->getFlag()}}" alt="{{$lang}}"
                                                                  class="h-full w-auto pe-2"></td>
                                <td class="px-2 text-center">{!! Condition::from($cond)->getShortHandRender() !!}</td>
                                <td class="px-2 text-center">{!! $isFirstEd ? '<span>✔️</span>' : '' !!}</td>
                                <td class="px-2 text-center">
                                    <button class="dark:bg-green-800 hover:bg-green-700 text-white font-bold py-1 px-4 rounded"
                                            wire:click="collect({{$variantId}},'{{$lang}}','{{$cond}}',{{$isFirstEd}})">Collect
                                    </button>
                                    <button class="dark:bg-red-800 hover:bg-red-700 text-white font-bold py-1 px-4 rounded"
                                            wire:click="trade({{$variantId}},'{{$lang}}','{{$cond}}',{{$isFirstEd}})">Trade
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
            </tbody>
        </table>
        <button class="dark:bg-green-800 hover:bg-green-700 text-white font-bold py-1 px-4 rounded"
                wire:click="autofill">Autofill
        </button>
        <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="save">
            Save
        </button>
        <p>Total Collectable: {{$totalCollectable}}</p>
        <p>Total Tradable: {{$totalTradable}}</p>
        <p>Total Not Set: {{$totalNotSet}}</p>
    </div>
</div>
