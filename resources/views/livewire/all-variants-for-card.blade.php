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
    <h1 class="text-2xl font-bold">
        {{$card->name}}
    </h1>
    <div class="py-5 grid gap-3 grid-cols-1 md:grid-cols-3 lg:grid-cols-6 xl:grid-cols-7 grid-rows-1">
        @foreach($card->variantCards as $variantCard)
            <div class="flex text-stone-400 relative items-start p-4 my-5">
                <div class="flex flex-col w-100">
                    <a href="/single-variant-card/{{$variantCard->id}}">
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
                @if (file_exists(public_path('storage/'. $variantCard->passcode . '.jpg')))
                    {{asset('storage/'. $variantCard->passcode . '.jpg')}}
                @elseif (file_exists(public_path('storage/'. $variantCard->passcode . '.png')))
                    {{asset('storage/'. $variantCard->passcode . '.png')}}
                @endif
            "
                        >
                    </a>
                    @if(!$variantCard->is_original)
                        <div class="absolute bg-red-200 w-[40px] h-[40px] rounded-full top-[-10px] right-[-10px] text-white flex items-center justify-center">alt</div>
                    @endif
                    <h2 id="card-{{$variantCard->id}}"
                        class="text-lg text-center font-bold text-stone-800 cursor-pointer hover:text-stone-500 pt-2"
                        onclick="copyName('card-{{$variantCard->id}}')">{{ $card->name }}</h2>
                    <p id="id-{{$variantCard->id}}"
                       class="text-md text-center font-bold text-stone-500 cursor-pointer hover:text-stone-400"
                       onclick="copyName('id-{{$variantCard->id}}')">{{ $variantCard->passcode }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
