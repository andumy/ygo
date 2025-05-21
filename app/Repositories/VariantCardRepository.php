<?php

namespace App\Repositories;

use App\Models\VariantCard;
use Illuminate\Database\Eloquent\Builder;

class VariantCardRepository
{
    public function findById(string $ygoId, ?bool $isOriginal = null): ?VariantCard
    {
        return VariantCard::where('ygo_id', $ygoId)
            ->when($isOriginal !== null, fn(Builder $q) => $q->where('is_original', $isOriginal))
            ->first();
    }

    public function create(string $ygoId, bool $isOriginal): VariantCard
    {
        return VariantCard::create([
            'ygo_id' => $ygoId,
            'is_original' => $isOriginal,
        ]);
    }
}
