<?php

namespace App\Livewire;

use App\Enums\Games;
use App\Repositories\GameRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class GameSelector extends Component
{
    public int $selectedGame;
    public Collection $availableGames;

    public function mount(GameRepository $gameRepository)
    {
        $this->availableGames = $gameRepository->getAllGames();
        $this->selectedGame = Session::get('game_id') ?? $gameRepository->findForGame(Games::YGO)?->id;
    }

    public function updatedSelectedGame($gameId)
    {
        Session::put('game_id', $gameId);
        $this->redirect('#', navigate: true);
    }
    public function render()
    {
        return view('livewire.game-selector');
    }
}
