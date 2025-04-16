@php use App\Enums\Lang; @endphp
<div class="p-10 text-stone-700">
    <div class="pb-10">
        <h1 class="text-2xl font-bold">
            YU-GI-OH! Library
        </h1>
        <div class="flex pt-2 justify-between">
            <div class="flex-col">
                <div class="flex">
                    <h3 class="text-lg font-bold text-cyan-400">
                        {{$metrics['total_cards']['owned']}} Owned
                    </h3>
                    <p class="px-5 text-lg">/</p>
                    <h3 class="text-lg font-bold">
                        {{$metrics['total_cards']['total']}} Total
                    </h3>
                    <h3 class="text-lg font-bold ps-10 text-stone-300">
                        ({{$metrics['total_cards']['percentage']}} %)
                    </h3>
                </div>
                <div class="flex">
                    <h3 class="text-lg font-bold text-cyan-400">
                        {{$metrics['total_instances']['owned']}} Owned Instances
                    </h3>
                    <p class="px-5 text-lg">/</p>
                    <h3 class="text-lg font-bold">
                        {{$metrics['total_instances']['total']}} Total Instances
                    </h3>
                    <h3 class="text-lg font-bold ps-10 text-stone-300">
                        ({{$metrics['total_instances']['percentage']}} %)
                    </h3>
                </div>
            </div>
            <div class="flex flex-col">
                <h3 class="text-lg font-bold text-cyan-700">
                    {{$metrics['total_owned_physical_cards']['owned']}} Physical Cards ({{$metrics['total_owned_physical_cards']['tradable']}} tradable)
                </h3>
                <div class="pt-2">
                    <p class="text-lg font-bold text-cyan-700">Estimated value: {{$metrics['total_owned_physical_cards']['estimated_cost']}} €</p>
                </div>
            </div>
        </div>
    </div>
    <div class="py-10 flex flex-col xl:flex-row justify-center xl:justify-end">
        <select id="onlyOwned" name="onlyOwned" wire:model="onlyOwned" wire:change="refresh" class="my-4 xl:mx-4 xl:my-0">
            <option value="all">All</option>
            <option value="owned">Owned</option>
            <option value="missing">Missing</option>
        </select>
        <select wire:model="set" wire:change="refresh" class="my-4 xl:mx-4 xl:my-0">
            <option value="">All sets</option>
            @foreach($sets as $s)
                <option value="{{$s->name}}">
                    {{$s->code}} : {{$s->name}}
                </option>
            @endforeach
        </select>
        <input
            class="appearance-none border rounded text-black"
            type="text"
            wire:model="search"
            wire:keyup="refresh"
            placeholder="Search"
        >
    </div>
    {{$cards->links()}}
    <div class="py-5 grid gap-3 grid-cols-1 md:grid-cols-3 lg:grid-cols-6 xl:grid-cols-7 grid-rows-1">
        @foreach($cards as $card)
            @include('components.card', ['card' => $card])
        @endforeach
    </div>
    {{$cards->links()}}
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
