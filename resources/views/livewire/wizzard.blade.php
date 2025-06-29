<div class="px-5">
    <select wire:model="set" wire:change="refresh" class="my-4 xl:mx-4 xl:my-0">
        <option value="">All sets</option>
        @foreach($sets as $s)
            <option {{$set === $s['name'] ? 'selected' : ''}} value="{{$s['name']}}">
                {{$s['code']}} : {{$s['name']}}
            </option>
        @endforeach
    </select>
    <table class="min-w-full bg-white border border-gray-300">
        <thead class="bg-gray-100">
        <tr>
            <th class="px-4 py-2 border">Card text</th>
        </tr>
        </thead>
        <tbody>
        @foreach($catalogMatches as $catalogMatch)
            @foreach($catalogMatch as $catalog)
                <tr>
                    <td class="px-4 py-2 border">{{ explode('(V.',$catalog->name)[0] }} ({{ $catalog->expansion }})</td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
    @foreach($cards as $card)
        <p>{{$card->name}}</p>
    @endforeach
    {{$cards->links()}}
</div>
