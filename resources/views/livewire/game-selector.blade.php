<div>
    <select wire:model.live="selectedGame" class="form-control min-w-50">
        @foreach($availableGames as $game)
            <option value="{{ $game->id() }}">{{ $game->pretty() }}</option>
        @endforeach
    </select>
</div>
