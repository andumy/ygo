<?php

namespace App\Repositories;

use App\Enums\Rarities;
use App\Models\Catalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CatalogRepository
{
    public function truncate(): void
    {
        Catalog::query()->truncate();
    }

    public function create(
        string $name,
        int $cardMarketId,
        ?string $number,
        ?string $rarity,
        ?string $expansion,
        ?string $expansionCode
    ): Catalog
    {
        return Catalog::create([
            'name' => $name,
            'cardmarket_id' => $cardMarketId,
            'number' => $number,
            'rarity' => $rarity,
            'expansion' => $expansion,
            'expansion_code' => $expansionCode
        ]);
    }

    /** @return Collection<Catalog> */
    public function search(
        ?string $expansionCode = null,
        ?string $number = null,
        ?Rarities $rarity = null,
        ?string $name = null,
    ): Collection
    {
        return Catalog::query()
            ->when($rarity, fn(Builder $q) => $q->where('rarity', $rarity->value))
            ->when($expansionCode, fn(Builder $q) => $q->where('expansion_code', $expansionCode))
            ->when($name, fn(Builder $q) => $q->where('name', 'like', "%$name%"))
            ->when($number, fn(Builder $q) => $q->where('number', 'like',  "%$number%"))
            ->get();
    }
}
