<?php

namespace App\Livewire;

use App\Models\Card;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OwnedCardRepository;
use App\Repositories\SetRepository;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use function round;
use function trim;

class Cards extends Component
{
    use WithPagination;

    /** LW Models */
    public string $onlyOwned = 'all';
    public string $set = '';

    public Collection $sets;
    public string $search = '';

    protected $queryString = ['search'];

    private CardRepository $cardRepository;
    private OrderRepository $orderRepository;
    private CardInstanceRepository $cardInstanceRepository;
    private OwnedCardRepository $ownedCardRepository;
    private SetRepository $setRepository;

    public function boot(
        CardRepository $cardRepository,
        CardInstanceRepository $cardInstanceRepository,
        OwnedCardRepository $ownedCardRepository,
        SetRepository $setRepository,
        OrderRepository $orderRepository,
    )
    {
        $this->cardRepository = $cardRepository;
        $this->cardInstanceRepository = $cardInstanceRepository;
        $this->ownedCardRepository = $ownedCardRepository;
        $this->setRepository = $setRepository;
        $this->orderRepository = $orderRepository;
    }

    private function getOwnOnlyAsBool(): ?bool
    {
        return match ($this->onlyOwned) {
            'all' => null,
            'missing' => false,
            'owned' => true,
            default => throw new \Exception('Unexpected match value'),
        };
    }

    public function refresh()
    {
    }

    public function mount(): void
    {
        $this->sets = $this->setRepository->all();
    }

    public function render()
    {
        /** @var Collection<Card> $cards */
        $cards = $this->cardRepository->paginate(
            search: trim($this->search),
            set: $this->set,
            pagination: 100,
            onlyOwned: $this->getOwnOnlyAsBool(),
        );

        return view('livewire.cards', [
            'metrics' => [
                'total_cards' => [
                    'owned' => $totalCardsOwned = $this->getOwnOnlyAsBool() === false ? 0 : $this->cardRepository->count(
                        search: trim($this->search),
                        set: $this->set,
                        onlyOwned: true,
                    ),
                    'total' => $totalCardsTotal = $this->cardRepository->count(
                        search: trim($this->search),
                        set: $this->set,
                        onlyOwned: $this->getOwnOnlyAsBool(),
                    ),
                    'percentage' => $totalCardsTotal != 0 ?
                        round($totalCardsOwned / $totalCardsTotal * 100,2) : 0,
                ],
                'total_instances' => [
                    'owned' => $totalInstancesOwned = $this->getOwnOnlyAsBool() === false ? 0 : $this->cardInstanceRepository->count(
                        search: trim($this->search),
                        set: $this->set,
                        onlyOwned: true,
                    ),
                    'total' => $totalInstancesTotal = $this->cardInstanceRepository->count(
                        search: trim($this->search),
                        set: $this->set,
                        onlyOwned: $this->getOwnOnlyAsBool(),
                    ),
                    'percentage' => $totalInstancesTotal != 0 ?
                        round($totalInstancesOwned / $totalInstancesTotal * 100,2) : 0,
                ],
                'total_owned_physical_cards' => [
                    'owned' => $this->ownedCardRepository->count(),
                    'tradable' =>  $this->ownedCardRepository->countTradable(),
                    'estimated_cost' => $this->cardInstanceRepository->priceForOwnOrOrder(),
                ]
            ],
            'cards' => $cards,
            'setCode' => $this->setRepository->findByName($this->set)?->code ?? '',
        ]);
    }
}
