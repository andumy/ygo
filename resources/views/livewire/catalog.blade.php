@php
    use App\Dtos\CatalogMatch;

    /**
     * @var CatalogMatch[] $catalogMatches
     */
@endphp
<div class="flex flex-col w-full items-center">
    <p class="bg-green-300 text-stone-700">
        {{$message}}
    </p>
    <form wire:submit.prevent="loadCatalog"
          class="relative flex flex-col my-6 bg-white shadow-sm border border-slate-200 rounded-lg w-1/2 justify-center items-center py-20">
        <label
            class="dark:bg-white border border-gray-700 hover:bg-gray-700 text-gray-700 hover:text-white font-bold py-1 px-4 rounded cursor-pointer mb-5"
            for="file">Upload a catalog</label>
        <input type="file" wire:model="file" class="hidden" id="file">

        <button type="submit"
                class="hidden dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded cursor-pointer mb-5">
            Upload Catalog
        </button>
    </form>

    <div
        class="relative flex flex-col my-6 bg-white shadow-sm border border-slate-200 rounded-lg w-full justify-center items-center py-20">
        <button wire:click="generateListing"
                class="dark:bg-gray-800 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded cursor-pointer mb-5">
            Generate Listing
        </button>

        @if($catalogMatches)
            <table>
                <thead>
                <tr>
                    <th class="text-center px-2">Own Card Name</th>
                    <th class="text-center px-2">Own Card Set Code</th>
                    <th class="text-center px-2">Own Card Set Name</th>
                    <th class="text-center px-2">Own Card Rarity</th>
                    <th class="text-center px-2">Own Card Lang</th>
                    <th class="text-center px-2">Own Card Cond</th>
                    <th class="text-center px-2">Own Card FirstEd</th>
                    <th class="text-center px-2">Own Card Amount</th>
                    <th class="text-center px-2">||</th>
                    <th class="text-center px-2">Catalog Name</th>
                    <th class="text-center px-2">Catalog Set Code</th>
                    <th class="text-center px-2">Catalog Set Name</th>
                    <th class="text-center px-2">Catalog Rarity</th>
                    <th class="text-center px-2">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($catalogMatches as $index => $catalogMatch)
                    <tr class="py-2 border-b-2 {{$catalogMatch->selectedCatalog ? 'bg-green-200' : 'bg-red-200'}}">
                        <td class="text-center px-2">{{$catalogMatch->ownedCard->variant->cardInstance->card->name}}</td>
                        <td class="text-center px-2">{{$catalogMatch->ownedCard->variant->cardInstance->card_set_code}}</td>
                        <td class="text-center px-2">{{$catalogMatch->ownedCard->variant->cardInstance->set->name}}</td>
                        <td class="text-center px-2">{{$catalogMatch->ownedCard->variant->cardInstance->rarity_verbose->value}}</td>
                        <td class="text-center px-2">{{$catalogMatch->ownedCard->lang->value}}</td>
                        <td class="text-center px-2">{{$catalogMatch->ownedCard->cond->value}}</td>
                        <td class="text-center px-2">{!! $catalogMatch->ownedCard->is_first_edition ? '<span>✔️</span>' : '' !!}</td>
                        <td class="text-center px-2">{{$catalogMatch->ownedCard->amount}}</td>
                        <td class="text-center px-2">||</td>

                        @if($catalogMatch->selectedCatalog)
                            <td class="text-center px-2">
                                <p class="m-0 my-2">{{$catalogMatch->selectedCatalog->name}}</p>
                            </td>
                            <td class="text-center px-2">
                                <p class="m-0 my-2">{{$catalogMatch->selectedCatalog->expansion_code}}-{{$catalogMatch->selectedCatalog->number}}</p>
                            </td>
                            <td class="text-center px-2">
                                <p class="m-0 my-2">{{$catalogMatch->selectedCatalog->expansion}}</p>
                            </td>
                            <td class="text-center px-2">
                                <p class="m-0 my-2">{{$catalogMatch->selectedCatalog->rarity}}</p>
                            </td>
                            <td class="text-center px-2">
                            </td>
                        @else
                            <td colspan="5">
                                <table class="w-full">
                                    <tbody>
                                        @foreach($catalogMatch->catalogs as $catalog)
                                            <tr>
                                                <td class="text-center px-2" colspan="1">
                                                    <p class="m-0 my-2">{{$catalog->name}}</p>
                                                </td>
                                                <td class="text-center px-2" colspan="1">
                                                    <p class="m-0 my-2">{{$catalog->expansion_code}}-{{$catalog->number}}</p>
                                                </td>
                                                <td class="text-center px-2" colspan="1">
                                                    <p class="m-0 my-2">{{$catalog->expansion}}</p>
                                                </td>
                                                <td class="text-center px-2" colspan="1">
                                                    <p class="m-0 my-2">{{$catalog->rarity}}</p>
                                                </td>
                                                <td class="text-center px-2" colspan="1">
                                                    <button class="dark:bg-green-800 hover:bg-green-700 text-white font-bold py-1 px-4 rounded my-2"
                                                            wire:click="selectCatalog({{$index}},{{$catalog->cardmarket_id}})">Select
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>

            <h3 class="text-lg font-bold text-cyan-400">
                {{$total}} Total Cards
            </h3>

            <button wire:click="exportListing"
                    class="dark:bg-green-800 hover:bg-green-600 text-white font-bold py-1 px-4 rounded cursor-pointer mt-5">
                Export Listing
            </button>
        @endif
    </div>
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
