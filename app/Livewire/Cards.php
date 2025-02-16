<?php

namespace App\Livewire;

use App\Enums\Condition;
use App\Enums\Lang;
use App\Models\Card;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OwnedCardRepository;
use App\Repositories\PriceRepository;
use App\Repositories\SetRepository;
use App\Services\CardService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use function dd;
use function round;
use function trim;

class Cards extends Component
{
    use WithPagination;

    public ?bool $onlyOwned = null; // null = all, true = owned, false = not owned

    public string $lang = Lang::ENGLISH->value;
    public string $code = '';
    public string $set = '';
    public string $rarity = '';
    public array $rarities = [];
    public Collection $sets;
    public string $search = '';
    public string $message = '';

    public Collection $orders;
    public array $ownedCards = [];
    public array $prices = [];

    private CardRepository $cardRepository;
    private OrderRepository $orderRepository;
    private CardInstanceRepository $cardInstanceRepository;
    private OwnedCardRepository $ownedCardRepository;
    private CardService $cardService;
    private SetRepository $setRepository;
    private PriceRepository $priceRepository;

    public function boot(
        CardRepository $cardRepository,
        CardInstanceRepository $cardInstanceRepository,
        OwnedCardRepository $ownedCardRepository,
        CardService $cardService,
        SetRepository $setRepository,
        OrderRepository $orderRepository,
        PriceRepository $priceRepository,
    )
    {
        $this->cardRepository = $cardRepository;
        $this->cardInstanceRepository = $cardInstanceRepository;
        $this->ownedCardRepository = $ownedCardRepository;
        $this->cardService = $cardService;
        $this->setRepository = $setRepository;
        $this->orderRepository = $orderRepository;
        $this->priceRepository = $priceRepository;
    }

    public function updateOwn(int $instanceId): void
    {
        $batch = $this->ownedCardRepository->fetchNextBatch();
        $instance = $this->cardInstanceRepository->findById($instanceId);
        $owned = $this->ownedCards[$instanceId];

        foreach ($owned as $lang => $langArray) {
            foreach ($langArray as $cond => $condArray) {
                foreach ($condArray as $isFirstEdition => $amount) {
                    if($amount === ""){
                        continue;
                    }
                    $this->cardService->updateCardStockFromInstance(
                        cardInstance: $instance,
                        batch: $batch,
                        lang: Lang::from($lang),
                        condition: Condition::from($cond),
                        amount: (int)$amount,
                        isFirstEdition: $isFirstEdition,
                    );
                }
            }
        }
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
            pagination: 10,
            onlyOwned: $this->onlyOwned
        );

        foreach ($cards as $card){
            foreach ($card->cardInstances as $cardInstance) {
                $this->ownedCards[$cardInstance->id] = [];
                $this->prices[$cardInstance->id] = $cardInstance->price->price ?? 0;

                foreach ($this->ownedCardRepository->fetchByInstanceGroupByAllOverAmount($cardInstance->id) as $ownedCard){
                    $this->ownedCards
                        [$cardInstance->id]
                        [$ownedCard->lang->value]
                        [$ownedCard->cond->value]
                        [(int)$ownedCard->is_first_edition] =
                            $ownedCard->amount ?? 0;
                }
            }
        }

        return view('livewire.cards', [
            'metrics' => [
                'total_cards' => [
                    'owned' => $totalCardsOwned = $this->cardRepository->count(
                        search: trim($this->search),
                        set: $this->set,
                        onlyOwned: true
                    ),
                    'total' => $totalCardsTotal = $this->cardRepository->count(
                        search: trim($this->search),
                        set: $this->set,
                        onlyOwned: $this->onlyOwned
                    ),
                    'percentage' => $totalCardsTotal != 0 ?
                        round($totalCardsOwned / $totalCardsTotal * 100,2) : 0,
                ],
                'total_instances' => [
                    'owned' => $totalInstancesOwned = $this->cardInstanceRepository->count(
                        search: trim($this->search),
                        set: $this->set,
                        onlyOwned: true
                    ),
                    'total' => $totalInstancesTotal = $this->cardInstanceRepository->count(
                        search: trim($this->search),
                        set: $this->set,
                        onlyOwned: $this->onlyOwned
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
