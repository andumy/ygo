<?php

namespace App\Repositories;

use App\Models\VariantCard;
use Illuminate\Database\Eloquent\Builder;

class VariantCardRepository
{
    public function findById(string $passcode, ?bool $isOriginal = null): ?VariantCard
    {
        return VariantCard::where('passcode', $passcode)
            ->when($isOriginal !== null, fn(Builder $q) => $q->where('is_original', $isOriginal))
            ->first();
    }

    public function create(string $passcode, bool $isOriginal): VariantCard
    {
        return VariantCard::create([
            'passcode' => $passcode,
            'is_original' => $isOriginal,
        ]);
    }
}
