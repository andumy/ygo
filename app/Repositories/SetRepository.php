<?php

namespace App\Repositories;

use App\Enums\Sale;
use App\Models\Set;
use Illuminate\Support\Collection;

class SetRepository
{
    public function firstOrCreate(array $find, array $data): Set
    {
        return Set::firstOrCreate($find, $data);
    }

    public function findById(int $id): ?Set
    {
        return Set::find($id);
    }

    public function findByName(string $name): ?Set
    {
        return Set::where('name',$name)->orWhere('alias',$name)->first();
    }

    /** @return Collection<Set> */
    public function getByCode(string $code): Collection
    {
        return Set::where('code',$code)->get();
    }

    public function updateOrCreate(array $find, array $data): Set
    {
        return Set::updateOrCreate($find, $data);
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
