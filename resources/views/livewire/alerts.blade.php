@php
    use App\Models\Variant;
@endphp
<div>
    @if($tradedNotCollected->count() === 0)
        <p class="bg-green-300 text-stone-700">
            {{ __('No trades to collect') }}
        </p>
    @else
            <p class="bg-red-300 text-stone-700">
                {{ __('Not collected cards:') }}
            </p>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Variant Id') }}</th>
                        <th>{{ __('Card') }}</th>
                        <th>{{ __('Card set code') }}</th>
                        <th>{{ __('Rarity') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tradedNotCollected as $variant)
                        @php
                            /** @var Variant $variant */
                        @endphp
                        <tr>
                            <td>{{ $variant->id }}</td>
                            <td>{{ $variant->cardInstance->card->name }}</td>
                            <td>{{ $variant->cardInstance->card_set_code }}</td>
                            <td>{{ $variant->cardInstance->shortRarity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
    @endif

</div>
