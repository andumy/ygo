<?php

namespace App\Livewire;

use App\Models\CardInstance;
use App\Models\Set;
use App\Repositories\CardRepository;
use App\Repositories\SetRepository;
use Livewire\Component;
use function array_key_exists;
use function count;

class PurchaseRecommendation extends Component
{
    private SetRepository $setRepository;
    private CardRepository $cardRepository;

    public array $recommendedSets = [];
    public array $completionSets = [];
    public array $cis = [];

    public function boot(SetRepository $setRepository, CardRepository $cardRepository)
    {
        $this->setRepository = $setRepository;
        $this->cardRepository = $cardRepository;
    }


    public function mount()
    {
        $sets = $this->setRepository->all()->map(function (Set $set) {
            $total = $this->cardRepository->count(set: $set->name, includeVariants: false);
            $setOwned = $this->cardRepository->countOwnedAndOrderedInsideSet(set: $set->name);
            $owned = $this->cardRepository->count(set: $set->name, onlyOwned: 1, includeVariants: false);
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

        $this->recommendedSets = $sets->filter(fn($s) => $s['total'] > 10)
        ->sortBy([
            ['missing', 'desc']
        ])->toArray();

        $this->completionSets = $sets->filter(fn($s) => $s['total'] > 10)
        ->sortBy([
            ['setMissing', 'asc'],
            ['total', 'desc'],
        ])->toArray();

        $notOwnedCards = $this->cardRepository->searchQuery(onlyOwned: false, includeVariants: false)->get();
        $this->cis = [];
        foreach ($notOwnedCards as $card) {
            /** @var CardInstance $ci */
            $ci = $card->cardInstances()
                ->join('prices', 'card_instances.id', '=', 'prices.card_instance_id')
                ->orderBy('prices.price', 'asc')
                ->first(['card_instances.*']);

            if(!$ci){
                continue;
            }

            if(!array_key_exists($ci->set_id, $this->cis)){
                $this->cis[$ci->set_id] = [];
            }
            $this->cis[$ci->set_id][] = $ci;
        }
        usort($this->cis, function($a, $b) { return count($b) - count($a); });

    }
    public function render()
    {
        return view('livewire.purchase-recommendation');
    }
}
