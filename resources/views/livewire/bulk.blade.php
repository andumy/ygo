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
                        @foreach($condArray as $isFirstEd => $cardObject)
                            @php
                            try{
                            @endphp
                            <tr class="py-2 border-b-2">
                                <td class="text-center px-2">{{$cardObject['cardInstance']->first()->card->name}}</td>
                                <td class="text-center px-2">{{$code}}</td>
                                <td class="text-center px-2"><img src="{{Lang::from($lang)->getFlag()}}" alt="{{$lang}}"
                                                                  class="h-[14px]"></td>
                                <td class="text-center px-2">{!! Condition::from($cond)->getShortHand() !!}</td>
                                <td class="text-center px-2">{!! $isFirstEd ? '<span>✔️</span>' : '' !!}</td>
                                @if($cardObject['cardInstance']->count() == 1)
                                    <td class="text-center px-2
                                    {{
                                            $cardObject['cardInstance']->first()->isOwnedForLang(Lang::from($lang)) ? 'text-orange-700' : (
                                                $cardObject['cardInstance']->first()->isOwned ? 'text-amber-400' : (
                                                    $cardObject['cardInstance']->first()->card->isOwned ? 'text-teal-400' : ''
                                                )
                                            )
                                    }}
                                    ">{{$cardObject['cardInstance']->first()->rarity_verbose->value}}</td>
                                @else
                                    <td class="text-center px-2">
                                        <div class="flex flex-col">
                                            @foreach($cardObject['cardInstance'] as $ci)
                                                <p class="
                                                    {{
                                                        $ci->isOwnedForLang(Lang::from($lang)) ? 'text-orange-700' : (
                                                            $ci->isOwned ? 'text-amber-400' : (
                                                                $ci->card->isOwned ? 'text-teal-400' : ''
                                                            )
                                                        )
                                                    }}
                                                ">{{$ci->rarity_verbose->value}}({{$ci->card->ygo_id}})</p>
                                            @endforeach
                                        </div>
                                    </td>
                                @endif
                                <td class="text-center px-2">
                                    @if($cardObject['cardInstance']->count() == 1)
                                        {{$cardObject['amount']}}
                                    @else
                                        <div class="flex items-center">
                                            <div class="flex flex-col">
                                                @foreach($cardObject['cardInstance'] as $ci)
                                                    <input class="border" type="number" wire:model="amounts.{{$code}}.{{$lang}}.{{$cond}}.{{$isFirstEd}}.{{$ci->id}}" id="{{$ci->id}}">
                                                @endforeach
                                            </div>
                                            <p>Out of Max {{$cardObject['amount']}}</p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @php
                                }catch (Exception $e){
                                    dd($code);
                                }
                            @endphp
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
            </tbody>
        </table>
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
