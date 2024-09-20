<?php

namespace App\Repositories;

use App\Models\CardInstance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use function dd;

class CardInstanceRepository
{
    public function create(array $data): CardInstance
    {
        return CardInstance::create($data);
    }

    public function firstOrCreate(array $find, array $data): CardInstance
    {
        return CardInstance::firstOrCreate($find, $data);
    }

    /** @return Collection<CardInstance> */
    public function findBySetCode(string $code): Collection
    {
        return CardInstance::where('card_set_code', $code)->get();
    }

    /** @return Collection<CardInstance> */
    public function findBySetCodeAndRarity(string $code, string $rarity): Collection
    {
        return CardInstance::where('card_set_code', $code)->where('rarity_verbose', $rarity)->get();
    }

    public function findById(int $id): ?CardInstance
    {
        return CardInstance::find($id);
    }

    public function rarities(): Collection
    {
        return CardInstance::select('rarity_verbose')
            ->distinct()
            ->get()
            ->pluck('rarity_verbose');
    }

    /**
     * @return array{low: float, avg: float, high: float}
     */
    public function priceForOwnOrOrder(): array
    {
        return CardInstance::leftJoin('owned_cards', 'card_instances.id', '=', 'owned_cards.card_instance_id')
            ->leftJoin(
                DB::raw('(SELECT card_instance_id, SUM(amount) as total_ordered_amount FROM ordered_cards GROUP BY card_instance_id) as ordered_summary'), 'card_instances.id', '=', 'ordered_summary.card_instance_id')
            ->join('prices', 'card_instances.id', '=', 'prices.card_instance_id')
            ->selectRaw(
                'SUM((COALESCE(owned_cards.amount, 0) + COALESCE(ordered_summary.total_ordered_amount, 0)) * prices.low) as low,
                 SUM((COALESCE(owned_cards.amount, 0) + COALESCE(ordered_summary.total_ordered_amount, 0)) * prices.avg) as avg,
                 SUM((COALESCE(owned_cards.amount, 0) + COALESCE(ordered_summary.total_ordered_amount, 0)) * prices.high) as high'
            )
            ->first()
            ->toArray();
    }
}
