<?php
    use App\Models\CardInstance;
    use App\Models\Card;
    use App\Enums\Lang;
/**
 * @var CardInstance $cardInstance
 * @var Card $card
 */
?>

<div class="w-screen p-10 text-stone-700">
    <div class="pb-10">
        <h1 class="text-2xl font-bold">
            {{$card->name}}
        </h1>
    </div>
    @foreach($card->variantCards as $variantCard)
        <div class="flex text-stone-400 relative items-center border-b-2 border-cyan-700 p-4 my-5" data-hide-tooltip="true">
            <div class="flex flex-col w-100">
                <img
                    @if($variantCard->isMissing)
                        class="grayscale opacity-80 w-60"
                    @endif
                    @if($variantCard->isOrdered)
                        class="sepia opacity-80 w-60"
                    @endif
                    @if($variantCard->isOwned)
                        class="w-60"
                    @endif
                    src="
            @if (file_exists(public_path('storage/'. $variantCard->ygo_id . '.jpg')))
                {{asset('storage/'. $variantCard->ygo_id . '.jpg')}}
            @elseif (file_exists(public_path('storage/'. $variantCard->ygo_id . '.png')))
                {{asset('storage/'. $variantCard->ygo_id . '.png')}}
            @endif
        "
                >
                @if(!$variantCard->is_original)
                    <div class="absolute bg-red-600 w-[20px] h-[20px] rounded-full top-[-10px] left-[-10px]"></div>
                @endif
                <p id="id-{{$card->id}}"
                   class="text-md text-center font-bold text-stone-500 cursor-pointer hover:text-stone-400"
                   onclick="copyName('id-{{$card->id}}')">{{ $variantCard->ygo_id }}</p>
                <h2 id="card-{{$card->id}}"
                    class="text-lg text-center font-bold text-stone-800 cursor-pointer hover:text-stone-500 pb-2"
                    onclick="copyName('card-{{$card->id}}')">{{ $card->name }}</h2>
            </div>
            <div class="flex flex-col w-100 ps-5">
                @foreach($variantCard->variants as $variant)
                    <div class="flex flex-row justify-start items-center relative has-tooltip cursor-pointer
                    @if(!$variant->isMissing)
                         font-bold
                         @if($variant->isOwned)
                             text-cyan-500
                         @else
                            text-yellow-500
                         @endif
                    @endif">
                        @if($variant->isOrdered)
                            <div class="flex flex-col items-start justify-center">
                                @foreach($variant->orderAmountByLang as $lang => $amount)
                                    <div class="flex flex-row items-center justify-start">
                                        <p class="text-md text-yellow-500 px-1">{{ $amount }} </p>
                                        <img src="{{Lang::from($lang)->getFlag()}}" alt="{{$lang}}" class="h-[14px]">
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-md text-yellow-500 px-1"> x </p>
                        @endif

                        @if($variant->isOwned)
                            <div class="flex flex-col items-start justify-center">
                                @foreach($variant->ownAmountByLang as $lang => $amount)
                                    <div class="flex flex-row items-center justify-start">
                                        <p class="text-md text-cyan-500 px-1">{{ $amount }} </p>
                                        <img src="{{Lang::from($lang)->getFlag()}}" alt="{{$lang}}" class="h-[14px]">
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-md text-cyan-500 px-1"> x </p>
                        @endif

                        <p data-tooltip-target="{{$variant->id}}"
                           class="text-md m-0"> {{$variant->cardInstance->card_set_code}} {{$variant->cardInstance->shortRarity}}
                            : {{$variant->cardInstance->price?->price ?? '-'}} €</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
