<div>
    <select wire:model.live="selectedGame" class="form-control">
        @foreach($availableGames as $game)
            <option value="{{ $game->id }}">{{ $game->name }}</option>
        @endforeach
    </select>
</div>
