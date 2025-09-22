<?php

namespace App\Livewire;

use App\Enums\Games;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class GameSelector extends Component
{
    public int $selectedGame;
    public array $availableGames;

    public function mount()
    {
        $this->availableGames = Games::cases();
        if(!Session::get('game_id')) {
            Session::put('game_id', Games::YGO->id());
        }
        $this->selectedGame = Session::get('game_id');
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
