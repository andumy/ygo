@php use App\Enums\Condition;use App\Enums\Lang; @endphp
<div class="flex flex-col w-full items-center">
    <form wire:submit.prevent="loadSheet"
          class="relative flex flex-col my-6 bg-white shadow-sm border border-slate-200 rounded-lg w-1/2 justify-center items-center py-20">
        <label
                class="dark:bg-white border border-gray-700 hover:bg-gray-700 text-gray-700 hover:text-white font-bold py-1 px-4 rounded cursor-pointer mb-5"
                for="file">Select a spreadsheet</label>
        <input type="file" wire:model="file" class="hidden" id="file">

        <button type="submit"
                class="hidden dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded cursor-pointer mb-5">
            Bulk upload
        </button>
    </form>
    @if($cards)
        <table>
            <thead>
            <tr>
                <th class="text-center px-2">Card Name</th>
                <th class="text-center px-2">Card Code</th>
                <th class="text-center px-2">Language</th>
                <th class="text-center px-2">Condition</th>
                <th class="text-center px-2">1st Edition</th>
                <th class="text-center px-2">Rarity</th>
                <th class="text-center px-2">Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($cards as $code => $codeArray)
                @foreach($codeArray as $lang => $langArray)
                    @foreach($langArray as $cond => $condArray)
                        @foreach($condArray as $isFirstEd => $isFirstEdArray)
                            @foreach($isFirstEdArray as $rarity => $cardObject)
                                @php
                                    try{

                                @endphp
                                <tr class="py-2 border-b-2">
                                    <td class="text-center px-2">{{$cardObject['variants']->first()->cardInstance->card->name}}</td>
                                    <td class="text-center px-2">{{$code}}</td>
                                    <td class="text-center px-2"><img src="{{Lang::from($lang)->getFlag()}}" alt="{{$lang}}"
                                                                      class="h-[14px]"></td>
                                    <td class="text-center px-2">{!! Condition::from($cond)->getShortHandRender() !!}</td>
                                    <td class="text-center px-2">{!! $isFirstEd ? '<span>✔️</span>' : '' !!}</td>
                                    @if($cardObject['variants']->count() == 1)
                                        <td class="text-center px-2
                                        {{
                                                $cardObject['variants']->first()->isOwnedForLang(Lang::from($lang)) ? 'text-orange-700' : (
                                                    $cardObject['variants']->first()->isOwned ? 'text-amber-400' : (
                                                        $cardObject['variants']->first()->cardInstance->card->isOwned ? 'text-teal-400' : ''
                                                    )
                                                )
                                        }}
                                        ">{{$cardObject['variants']->first()->cardInstance->shortRarity}}</td>
                                    @else
                                        <td class="text-center px-2">
                                            <div class="flex flex-col">
                                                @foreach($cardObject['variants'] as $variant)
                                                    <p class="
                                                        {{
                                                            $variant->isOwnedForLang(Lang::from($lang)) ? 'text-orange-700' : (
                                                                $variant->isOwned ? 'text-amber-400' : (
                                                                    $variant->cardInstance->card->isOwned ? 'text-teal-400' : ''
                                                                )
                                                            )
                                                        }}
                                                    ">{{$variant->cardInstance->shortRarity}}({{$variant->variantCard->passcode}})</p>
                                                @endforeach
                                            </div>
                                        </td>
                                    @endif
                                    <td class="text-center px-2">
                                        @if($cardObject['variants']->count() == 1)
                                            {{$cardObject['amount']}}
                                        @else
                                            <div class="flex items-center">
                                                <div class="flex flex-col">
                                                    @foreach($cardObject['variants'] as $index => $variant)
                                                        <input class="border" type="number"
                                                               wire:model="amounts.{{$code}}.{{$lang}}.{{$cond}}.{{$isFirstEd}}.{{$rarity}}.{{$variant->id}}"
                                                               id="{{$variant->id}}">
                                                    @endforeach
                                                </div>
                                                <p>Out of Max {{$cardObject['amount']}}</p>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @php
                                    }catch (Exception $e){
                                        dump($code);
                                    }
                                @endphp
                          @endforeach
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
            </tbody>
        </table>
        <h3 class="text-lg font-bold text-cyan-400">
            {{$total}} Total Cards
        </h3>
    @endif
    <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded" wire:click="save">
        Save
    </button>
</div>

@script
<script>
    const handleText = () => {
        const fileName = document.querySelector('input').value.split('\\').pop();
        if (fileName !== '') {
            document.querySelector('label').innerText = document.querySelector('input').value.split('\\').pop()
            document.querySelector('button').classList.remove('hidden');
        }
    }

    Livewire.hook('morph.updated', ({el, component}) => {
        handleText()
    })
</script>
@endscript
