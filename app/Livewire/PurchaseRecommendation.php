<?php

namespace App\Livewire;

use App\Models\Set;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\SetRepository;
use Livewire\Component;
use function dd;

class PurchaseRecommendation extends Component
{
    private SetRepository $setRepository;
    private CardRepository $cardRepository;

    public array $recommendedSets = [];
    public array $completionSets = [];

    public function boot(SetRepository $setRepository, CardRepository $cardRepository)
    {
        $this->setRepository = $setRepository;
        $this->cardRepository = $cardRepository;
    }


    public function mount()
    {
        $sets = $this->setRepository->all()->map(function (Set $set) {
            $total = $this->cardRepository->count(set: $set->name);
            $setOwned = $this->cardRepository->countOwnedAndOrderedInsideSet(set: $set->name);
            $owned = $this->cardRepository->count(set: $set->name, ownedFilter: 1);
            return [
                'code' => $set->code,
                'name' => $set->name,
                'total' => $total,
                'owned' => $owned,
                'setOwned' => $setOwned,
                'missing' => $total - $owned,
                'setMissing' => $total - $setOwned,
            ];
        })->filter(fn ($s) => $s['total'] > 0 && $s['total'] !== $s['owned']);

        $this->recommendedSets = $sets->sortBy([
            ['missing', 'desc']
        ])->toArray();

        $this->completionSets = $sets->filter(fn($s) => $s['total'] > 10)
        ->sortBy([
            ['setMissing', 'asc'],
            ['total', 'desc'],
        ])->toArray();
    }
    public function render()
    {
        return view('livewire.purchase-recommendation');
    }
}
