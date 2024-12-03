<?php

namespace App\Livewire;

use App\Events\StockUpdateEvent;
use App\Repositories\CardInstanceRepository;
use App\Repositories\OwnedCardRepository;
use App\Repositories\SetRepository;
use App\Repositories\TradableCardRepository;
use Illuminate\Support\Collection;
use Livewire\Component;
use function array_reduce;

class TradableCards extends Component
{

    public string $set = '';
    public array $cardInstances = [];
    public string $message = '';

    public Collection $sets;

    private CardInstanceRepository $cardInstanceRepository;
    private OwnedCardRepository $ownedCardRepository;
    private SetRepository $setRepository;
    private TradableCardRepository $tradableCardRepository;

    public function boot(
        CardInstanceRepository $cardInstanceRepository,
        SetRepository $setRepository,
        TradableCardRepository $tradableCardRepository,
        OwnedCardRepository $ownedCardRepository,
    )
    {
        $this->cardInstanceRepository = $cardInstanceRepository;
        $this->setRepository = $setRepository;
        $this->tradableCardRepository = $tradableCardRepository;
        $this->ownedCardRepository = $ownedCardRepository;

        $this->sets = $this->setRepository->allWithCardsAndNewStock();
    }
    public function refresh(){
        $this->cardInstances = [];
        foreach ($this->cardInstanceRepository->search(set: $this->set, ownedFilter: 1) as $ci){
            $this->cardInstances[] = [
                'id' => $ci->id,
                'ygo_id' => $ci->card->ygo_id,
                'card_set_code' => $ci->card_set_code,
                'card_name' => $ci->card->name,
                'rarity' => $ci->rarity_verbose,
                'total' => $ci->ownedCard?->amount,
                'collectable' => $ci->tradableCard?->collectable_amount ?? 0,
                'tradable' => $ci->tradableCard?->tradable_amount ?? 0,
                'valid' =>  $ci->tradableCard?->collectable_amount ?? 0 + $ci->tradableCard?->tradable_amount ?? 0 ==
                    $ci->ownedCard?->amount,
            ];
        }

    }

    public function revalidate(){
        foreach ($this->cardInstances as $key => $ci){
            $this->cardInstances[$key]['valid'] = $ci['collectable'] + $ci['tradable'] == $ci['total'];
        }
    }

    public function autofill(){
        foreach ($this->cardInstances as $key => $ci){
            $this->cardInstances[$key]['collectable'] = 1;
            $this->cardInstances[$key]['tradable'] = $ci['total'] - 1;
        }
        $this->revalidate();
    }

    public function save(){
        foreach ($this->cardInstances as $ci){
            $this->tradableCardRepository->updateOrCreate(
                ['card_instance_id' => $ci['id']],
                [
                    'collectable_amount' => $ci['collectable'],
                    'tradable_amount' => $ci['tradable'],
                ]
            );
            if($ci['collectable'] + $ci['tradable'] != $ci['total']){
                $cardInstance = $this->cardInstanceRepository->findById($ci['id']);
                $this->ownedCardRepository->updateAmount($cardInstance->ownedCard, $ci['collectable'] + $ci['tradable']);
            }
        }

        StockUpdateEvent::dispatch(
            $this->setRepository->findByName($this->set),
            false
        );

        $this->message = 'Set saved';
        $this->sets = $this->setRepository->allWithCardsAndNewStock();
        $this->set = $this->sets->first()->name;
        $this->refresh();
    }

    public function render()
    {
        $this->revalidate();
        return view('livewire.tradable-cards', [
            'totalCollectable' => array_reduce($this->cardInstances, fn($carry, $item) => $carry + $item['collectable'], 0),
            'totalTradable' => array_reduce($this->cardInstances, fn($carry, $item) => $carry + $item['tradable'], 0),
        ]);
    }
}
