<?php

namespace App\Repositories;

use App\Enums\Games;
use App\Models\Game;
use Illuminate\Support\Collection;

class GameRepository
{
    public function findForGame(Games $game): ?Game {
        return Game::where('name', $game)->first();
    }

    /**
     * @return Collection<Game> 
     */
    public function getAllGames(): Collection {
        return Game::all();
    }
}
