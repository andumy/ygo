@php
    use App\Models\Card;
    use App\Models\CardInstance;
    /**
    * @var Card $card
    * @var CardInstance $instance
    * */
@endphp
<div class="flex text-stone-400 relative items-start p-4 my-5">
    <div class="flex flex-col">
        <a href="/card/{{$card->id}}">
            <img
                @if($card->isMissing)
                    class="grayscale opacity-80 w-60"
                @endif
                @if($card->isOrdered)
                    class="sepia opacity-80 w-60"
                @endif
                @if($card->isOwned)
                    @if($setCode && $setCode !== "" && !$card->cardInstances->contains(fn($ci) => str_contains($ci->card_set_code,$setCode) && $ci->ownedCards->count() > 0))
                        class="opacity-50 w-60"
                @else
                    class="w-60"
                @endif
                @endif
                src="
                @if (file_exists(public_path('storage/'. $card->ygoId . '.jpg')))
                    {{asset('storage/'. $card->ygoId . '.jpg')}}
                @elseif (file_exists(public_path('storage/'. $card->ygoId . '.png')))
                    {{asset('storage/'. $card->ygoId . '.png')}}
                @endif
            "
            >
        </a>
        @if($card->card_id)
            <div class="absolute bg-red-600 w-[20px] h-[20px] rounded-full top-[-10px] left-[-10px]"></div>
        @endif
        <h2 id="card-{{$card->id}}"
            class="text-lg text-center font-bold text-stone-800 cursor-pointer hover:text-stone-500 pt-2"
            onclick="copyName('card-{{$card->id}}')">{{ $card->name }}</h2>
        @if($set)
            <h4 id="code-{{$card->id}}"
                class="text-sm text-center text-stone-500 hover:text-stone-3 cursor-pointer hover:text-stone-400 pt-2"
                onclick="copyName('code-{{$card->id}}')">{{ $card->codeForSet($set) }}</h4>
        @endif
    </div>

{{--    <div class="flex flex-col w-100 ps-5">--}}
{{--        @foreach($card->cardInstances->filter(fn($ci) => !($setCode && $setCode !== "" && !str_contains($ci->card_set_code,$setCode))) as $instance)--}}
{{--            <div id="tooltip-{{$instance->id}}"--}}
{{--                 class="js-tooltip absolute hidden bg-white rounded-xl z-50 flex p-2 top-5 right-0 text-stone-800 font-normal">--}}
{{--                <div class="flex flex-col">--}}
{{--                    <div class="flex justify-center items-center">--}}
{{--                        <b>{{$instance->card_set_code}}</b>--}}
{{--                    </div>--}}
{{--                    <div class="p-2 flex flex-row">--}}
{{--                        @foreach(Lang::cases() as $index => $lang)--}}
{{--                            @if($index % 6 == 0)--}}
{{--                                <div class="flex flex-col p-2">--}}
{{--                                    @endif--}}
{{--                                    <div class="flex justify-center items-center pb-4">--}}
{{--                                        <div class="flex flex-col justify-between items-center h-full">--}}
{{--                                            <img src="{{$lang->getFlag()}}" alt="{{$lang->value}}"--}}
{{--                                                 class="h-[20px] w-auto pe-2">--}}
{{--                                            <span>1st</span>--}}
{{--                                        </div>--}}
{{--                                        @foreach(Condition::cases() as $condition)--}}
{{--                                            <div class="flex flex-col px-1">--}}
{{--                                                {!! $condition->getShortHandRender() !!}--}}
{{--                                                <input--}}
{{--                                                    class="appearance-none border rounded text-black w-[50px] my-2"--}}
{{--                                                    type="number"--}}
{{--                                                    wire:model="ownedCards.{{$instance->id}}.{{$lang->value}}.{{$condition->value}}.0"--}}
{{--                                                    id="ownedCards.{{$instance->id}}.{{$lang->value}}.{{$condition->value}}.0"--}}
{{--                                                >--}}
{{--                                                <input--}}
{{--                                                    class="appearance-none border rounded text-black w-[50px]"--}}
{{--                                                    type="number"--}}
{{--                                                    wire:model="ownedCards.{{$instance->id}}.{{$lang->value}}.{{$condition->value}}.1"--}}
{{--                                                    id="ownedCards.{{$instance->id}}.{{$lang->value}}.{{$condition->value}}.1"--}}
{{--                                                >--}}
{{--                                            </div>--}}
{{--                                        @endforeach--}}
{{--                                    </div>--}}
{{--                                    @if($index % 6 == 5)--}}
{{--                                </div>--}}
{{--                            @endif--}}
{{--                        @endforeach--}}
{{--                        @if(count(Lang::cases()) % 6 != 0)--}}
{{--                    </div>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--                <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded"--}}
{{--                        wire:click="updateOwn({{$instance->id}})">--}}
{{--                    Update Own--}}
{{--                </button>--}}
{{--            </div>--}}
{{--    </div>--}}
{{--    @endforeach--}}
{{--    </div>--}}
</div>


