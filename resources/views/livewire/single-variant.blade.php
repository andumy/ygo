<?php

use App\Enums\Lang;
use App\Enums\Condition;
use App\Models\Variant;

/**
 * @var Variant $variant
 */
?>

<div class="w-screen p-10 text-stone-700">
    <div class="pb-10 flex">
        <a href="/all-variants-for-card/{{$variant->cardInstance->card_id}}" class="hover:text-stone-400">
            <h1 class="text-2xl font-bold">
                {{$variant->cardInstance->card->name}}
            </h1>
        </a>
        <h1 class="text-2xl font-bold">
            &nbsp;-&nbsp;
        </h1>
        <a href="/single-variant-card/{{$variant->variant_card_id}}" class="hover:text-stone-400">
            <h1 class="text-2xl font-bold">
                {{$variant->variantCard->ygo_id}}
            </h1>
        </a>
        <h1 class="text-2xl font-bold">
            &nbsp;- {{$variant->cardInstance->card_set_code}} {{$variant->cardInstance->shortRarity}}
        </h1>
    </div>
    <div class="flex text-stone-400 relative items-start flex-col p-4 my-5"
         data-hide-tooltip="true">
        <div class="flex flex-col w-screen items-center">
            <img
                @if($variant->isMissing)
                    class="grayscale opacity-80 w-60"
                @endif
                @if($variant->isOrdered)
                    class="sepia opacity-80 w-60"
                @endif
                @if($variant->isOwned)
                    class="w-60"
                @endif
                src="
        @if (file_exists(public_path('storage/'. $variant->variantCard->ygo_id . '.jpg')))
            {{asset('storage/'. $variant->variantCard->ygo_id . '.jpg')}}
        @elseif (file_exists(public_path('storage/'. $variant->variantCard->ygo_id . '.png')))
            {{asset('storage/'. $variant->variantCard->ygo_id . '.png')}}
        @endif
    "
            >

            <h2 id="card-{{$variant->id}}"
                class="text-lg text-center font-bold text-stone-800 cursor-pointer hover:text-stone-500 pt-2"
                onclick="copyName('card-{{$variant->id}}')">{{ $variant->cardInstance->card->name }}</h2>
            <p id="id-{{$variant->id}}"
               class="text-md text-center font-bold text-stone-500 cursor-pointer hover:text-stone-400"
               onclick="copyName('id-{{$variant->id}}')">{{ $variant->variantCard->ygo_id }}</p>
            <h3 id="card-instance-{{$variant->id}}"
                class="text-md text-center text-stone-800 cursor-pointer hover:text-stone-500 pb-2"
                onclick="copyName('card-instance-{{$variant->id}}')">{{$variant->cardInstance->card_set_code}} {{$variant->cardInstance->shortRarity}}</h3>
        </div>
        <div class="flex flex-col w-screen items-center align-center py-4">
                <div class="js-tooltip bg-white rounded-xl z-50 flex p-2 top-5 right-0 text-stone-800 font-normal">
                    <div class="flex flex-col">
                        <div class="p-2 flex flex-row">
                            @foreach(Lang::cases() as $index => $lang)
                                @if($index % 6 == 0)
                                    <div class="flex flex-col p-2">
                                     @endif
                                        <div class="flex justify-center items-center pb-4">
                                            <div class="flex flex-col justify-between items-center h-full">
                                                <img src="{{$lang->getFlag()}}" alt="{{$lang->value}}"
                                                     class="h-[20px] w-auto pe-2">
                                                <span>1st</span>
                                            </div>
                                            @foreach(Condition::cases() as $condition)
                                                <div class="flex flex-col px-1">
                                                    {!! $condition->getShortHandRender() !!}
                                                    <input
                                                        class="appearance-none border rounded text-black w-[50px] my-2"
                                                        type="number"
{{--                                                        wire:model="ownedCards.{{$instance->id}}.{{$lang->value}}.{{$condition->value}}.0"--}}
{{--                                                        id="ownedCards.{{$instance->id}}.{{$lang->value}}.{{$condition->value}}.0"--}}
                                                    >
                                                    <input
                                                        class="appearance-none border rounded text-black w-[50px]"
                                                        type="number"
{{--                                                        wire:model="ownedCards.{{$instance->id}}.{{$lang->value}}.{{$condition->value}}.1"--}}
{{--                                                        id="ownedCards.{{$instance->id}}.{{$lang->value}}.{{$condition->value}}.1"--}}
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
                            wire:click="updateOwn({{$variant->id}})">
                        Update Own
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
