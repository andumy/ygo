<?php

namespace App\Livewire;

use App\Models\Set;
use App\Models\Variant;
use App\Repositories\CardRepository;
use App\Repositories\SetRepository;
use App\Repositories\VariantRepository;
use App\Services\ListService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;

class Wizzard extends Component
{
    public string $set = '';
    public array $sets;
    private LengthAwarePaginator $cards;
    private VariantRepository $variantRepository;
    private ListService $listService;
    private SetRepository $setRepository;
    private CardRepository $cardRepository;

    public function boot(
        VariantRepository $variantRepository,
        ListService $listService,
        SetRepository $setRepository,
        CardRepository $cardRepository
    ): void
    {
        $this->variantRepository = $variantRepository;
        $this->listService = $listService;
        $this->setRepository = $setRepository;
        $this->cardRepository = $cardRepository;

        $this->sets = $this->setRepository->all()->map(function (Set $set) {
            $total = $this->cardRepository->count(set: $set->name);
            $setOwned = $this->cardRepository->countOwnedAndOrderedInsideSet(set: $set->name);
            $owned = $this->cardRepository->count(set: $set->name, onlyOwned: 1);
            return [
                'code' => $set->code,
                'name' => $set->name,
                'total' => $total,
                'owned' => $owned,
                'setOwned' => $setOwned,
                'missing' => $total - $owned,
                'setMissing' => $total - $setOwned,
            ];
        })->filter(fn ($s) => $s['total'] > 10 && $s['total'] !== $s['owned'])
            ->sortBy([
                ['setMissing', 'asc'],
                ['total', 'desc'],
            ])->toArray();

        $this->cards = $this->cardRepository->paginate(
            pagination: 150,
            onlyOwned: false,
        );
    }

    public function refresh()
    {
    }

    public function render()
    {
        $catalogMatches = new Collection();
        if($this->set){
            $set = $this->setRepository->findByName($this->set);
            $this->variantRepository->getPurchasableVariants($set->id)->each(fn(Variant $variant) =>
                $catalogMatches->push(
                    $this->listService->getCatalogsMatch($variant)
                )
            );
        }

        return view('livewire.wizzard')->with(
            [
                'catalogMatches' => $catalogMatches,
                'cards' => $this->cards,
            ]
        );
    }
}
