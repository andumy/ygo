<?php

namespace App\Repositories;

use App\Enums\Sale;
use App\Models\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SetRepository
{
    public function firstOrCreate(array $find, array $data): Set
    {
        return Set::firstOrCreate($find, $data);
    }
    public function firstOrCreateWithGame(array $find, array $data): Set
    {
        return Set::withoutGlobalScope('game')->firstOrCreate($find, $data);
    }

    public function findById(int $id): ?Set
    {
        return Set::find($id);
    }

    public function findByName(string $name): ?Set
    {
        return Set::where('name',$name)->orWhere('alias',$name)->first();
    }

    public function findByNameAndGameId(string $name, int $gameId): ?Set
    {
        return Set::withoutGlobalScope('game')
            ->where(function (Builder $q) use($name) {
                $q->where('name', $name)
                    ->orWhere('alias', $name);
            })
            ->where('game_id', $gameId)
            ->first();
    }

    public function updateOrCreateByGame(array $find, array $data): Set
    {
        return Set::withoutGlobalScope('game')->updateOrCreate($find, $data);
    }

    public function all(): Collection
    {
        return Set::orderBy('code')->get();
    }

    public function allWithUnsetOwnedCards(): Collection
    {
        return Set::whereHas('cardInstances', function($q){
            $q->whereHas('ownedCards', function($q){
                $q->where('sale',Sale::NOT_SET)->whereNull('order_id');
            });
        })->orderBy('code')->get();
    }

    public function allWithOwnedCards(): Collection
    {
        return Set::whereHas('cardInstances', function($q){
            $q->whereHas('ownedCards');
        })->orderBy('code')->get();
    }
}
