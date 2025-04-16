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
    <div class="py-5 grid gap-3 grid-cols-1 md:grid-cols-3 lg:grid-cols-6 xl:grid-cols-7 grid-rows-1">
        @foreach($card->variantCards as $variantCard)
            <div class="flex text-stone-400 relative items-start p-4 my-5">
                <div class="flex flex-col w-100">
                    <a href="/variant/{{$variantCard->id}}">
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
                    </a>
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
            </div>
        @endforeach
    </div>
</div>
