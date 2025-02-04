@php
    use App\Enums\Condition;
    use App\Enums\Lang;
    use App\Models\Card;
    use App\Models\CardInstance;
    /**
    * @var Card $card
    * @var CardInstance $instance
    * */
@endphp
<div class="flex text-stone-400 relative items-center border-b-2 border-cyan-700 p-4 my-5" data-hide-tooltip="true">
    <div class="flex flex-col w-100">
        <img
            @if($card->isMissing)
                class="grayscale opacity-80 w-60"
            @endif
            @if($card->isOrdered)
                class="sepia opacity-80 w-60"
            @endif
            @if($card->isOwned)
                @if($setCode && $setCode !== "" && !$card->cardInstances->contains(fn($ci) => str_contains($ci->card_set_code,$setCode) && $ci->ownedCards->reduce(fn($c, $oc) => $c + $oc->amount,0) > 0))
                    class="opacity-50 w-60"
                @else
                    class="w-60"
                @endif
            @endif
            src="
                @if (file_exists(public_path('storage/'. $card->ygo_id . '.jpg')))
                    {{asset('storage/'. $card->ygo_id . '.jpg')}}
                @elseif (file_exists(public_path('storage/'. $card->ygo_id . '.png')))
                    {{asset('storage/'. $card->ygo_id . '.png')}}
                @endif
            "
        >
        @if($card->card_id)
            <div class="absolute bg-red-600 w-[20px] h-[20px] rounded-full top-[-10px] left-[-10px]"></div>
        @endif
        <p id="id-{{$card->id}}"
           class="text-md text-center font-bold text-stone-500 cursor-pointer hover:text-stone-400"
           onclick="copyName('id-{{$card->id}}')">{{ $card->ygo_id }}</p>
        <h2 id="card-{{$card->id}}"
            class="text-lg text-center font-bold text-stone-800 cursor-pointer hover:text-stone-500 pb-2"
            onclick="copyName('card-{{$card->id}}')">{{ $card->name }}</h2>
    </div>
    <div class="flex flex-col w-100 ps-5">
        @foreach($card->cardInstances->filter(fn($ci) => !($setCode && $setCode !== "" && !str_contains($ci->card_set_code,$setCode))) as $instance)
            <div class="flex flex-row justify-start items-center relative has-tooltip cursor-pointer
        @if(!$instance->isMissing)
             font-bold
             @if($instance->isOwned)
                 text-cyan-500
             @else
                text-yellow-500
             @endif
        @endif">
                @if($instance->isOrdered)
                    <div class="flex flex-col items-start justify-center">
                        @foreach($instance->orderAmountByLang as $lang => $amount)
                            <div class="flex flex-row items-center justify-start">
                                <p class="text-md text-yellow-500 px-1">{{ $amount }} </p>
                                <img src="{{Lang::from($lang)->getFlag()}}" alt="{{$lang}}" class="h-[14px]">
                            </div>
                        @endforeach
                    </div>
                    <p class="text-md text-yellow-500 px-1"> x </p>
                @endif

                @if($instance->isOwned)
                    <div class="flex flex-col items-start justify-center">
                        @foreach($instance->ownAmountByLang as $lang => $amount)
                            <div class="flex flex-row items-center justify-start">
                                <p class="text-md text-cyan-500 px-1">{{ $amount }} </p>
                                <img src="{{Lang::from($lang)->getFlag()}}" alt="{{$lang}}" class="h-[14px]">
                            </div>
                        @endforeach
                    </div>
                    <p class="text-md text-cyan-500 px-1"> x </p>
                @endif

                <p data-tooltip-target="{{$instance->id}}"
                   class="text-md m-0"> {{$instance->card_set_code}} {{$instance->rarity}}
                    : {{$instance->price?->price ?? '-'}} €</p>
            </div>
        @endforeach
    </div>

    <div class="flex flex-col w-100 ps-5">
        @foreach($card->cardInstances->filter(fn($ci) => !($setCode && $setCode !== "" && !str_contains($ci->card_set_code,$setCode))) as $instance)
            <div id="tooltip-{{$instance->id}}"
                 class="js-tooltip absolute hidden bg-white rounded-xl z-50 flex p-2 top-5 right-0 text-stone-800 font-normal">
                <div class="flex flex-col">
                    <div class="flex justify-center items-center">
                        <b>{{$instance->card_set_code}}</b>
                    </div>
                    <div class="p-2 flex flex-row">
                        @foreach(Lang::cases() as $index => $lang)
                            @if($index % 6 == 0)
                                <div class="flex flex-col p-2">
                                    @endif
                                    <div class="flex justify-center items-center pb-2">
                                        <img src="{{$lang->getFlag()}}" alt="{{$lang->value}}"
                                             class="h-[20px] w-auto pe-2">
                                        @foreach(Condition::cases() as $condition)
                                            <div class="flex flex-col">
                                                {!! $condition->getShortHand() !!}
                                                <input
                                                    class="appearance-none border rounded text-black w-[50px]"
                                                    type="number"
                                                    wire:model="ownedCards.{{$instance->id}}.{{$lang->value}}.{{$condition->value}}"
                                                    id="ownedCards.{{$instance->id}}.{{$lang->value}}.{{$condition->value}}"
                                                >
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($index % 6 == 5)
                                </div>
                            @endif
                        @endforeach
                        @if(count(Lang::cases()) % 6 != 0)
                    </div>
                    @endif
                </div>
                <button class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded"
                        wire:click="updateOwn({{$instance->id}})">
                    Update Own
                </button>
            </div>
    </div>
    @endforeach
</div>
</div>

<script>
    function registerTooltips() {
        document.body.addEventListener('click', function (event) {
            const tooltip = event.target.getAttribute('data-tooltip-target');
            const hideTooltip = event.target.getAttribute('data-hide-tooltip');

            if (hideTooltip) {
                hideAllTooltips();
            }

            if (!tooltip) {
                return;
            }
            hideAllTooltips();
            document.getElementById('tooltip-' + tooltip)?.classList.remove('hidden');
        });
    }

    function hideAllTooltips() {
        document.querySelectorAll('.js-tooltip').forEach((element) => {
            element.classList.add('hidden');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        registerTooltips();
    });
</script>
