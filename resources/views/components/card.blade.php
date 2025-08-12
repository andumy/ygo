@php
    use App\Enums\Games;use App\Models\Card;
    use App\Models\CardInstance;
    /**
    * @var Card $card
    * @var CardInstance $instance
    * */
@endphp
<div class="flex text-stone-400 relative items-start p-4 my-5">
    <div class="flex flex-col">
        <a href="/all-variants-for-card/{{$card->id}}">
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
                @if (file_exists(public_path('storage/'. $card->passcode . '.jpg')))
                    {{asset('storage/'. $card->passcode . '.jpg')}}
                @elseif (file_exists(public_path('storage/'. $card->passcode . '.png')))
                    {{asset('storage/'. $card->passcode . '.png')}}
                @else
                    @switch($card->game_id)
                        @case(Games::YGO->id())
                            {{asset('storage/ygo-back.jpg')}}
                        @break
                        @case(Games::MTG->id())
                            {{asset('storage/mtg-back.png')}}
                        @break
                        @case(Games::POKEMON->id())
                            {{asset('storage/pokemon-back.png')}}
                        @break
                        @case(Games::RIFTBOUND->id())
                            {{asset('storage/riftbound-back.png')}}
                        @break
                    @endswitch
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
</div>


