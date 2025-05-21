<?php

use App\Enums\Lang;
use App\Models\VariantCard;

/**
 * @var VariantCard $variantCard
 */
?>

<div class="p-10 text-stone-700">
    <div class="flex">
        <a href="/all-variants-for-card/{{$variantCard->cardInstances->first()->card_id}}" class="hover:text-stone-400">
            <h1 class="text-2xl font-bold">
                {{$variantCard->cardInstances->first()->card->name}}
            </h1>
        </a>
        <h1 class="text-2xl font-bold">
            &nbsp;- {{$variantCard->ygo_id}}
        </h1>
    </div>
    <div class="flex text-stone-400 relative items-start flex-col p-4">
        <div class="flex flex-col items-center w-[100%]">
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
            <h2 id="card-{{$variantCard->id}}"
                class="text-lg text-center font-bold text-stone-800 cursor-pointer hover:text-stone-500 pt-2"
                onclick="copyName('card-{{$variantCard->id}}')">{{ $variantCard->cardInstances->first()->card->name }}</h2>
            <p id="id-{{$variantCard->id}}"
               class="text-md text-center font-bold text-stone-500 cursor-pointer hover:text-stone-400"
               onclick="copyName('id-{{$variantCard->id}}')">{{ $variantCard->ygo_id }}</p>

        </div>
        <div class="flex flex-col ps-5 w-[100%]">
            <table>
                <tbody>
                    @foreach($variantCard->variantsOrderedByCode as $variant)
                        <tr class="py-2 border-b-2">
                            <td>
                                <a href="/single-variant/{{$variant->id}}" class="flex flex-row justify-start items-center relative py-1
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

                                    <p class="text-md m-0"> {{$variant->cardInstance->card_set_code}} ({{$variant->cardInstance->rarity_verbose}})
                                        : {{$variant->cardInstance->price?->price ?? '-'}} €</p>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>
