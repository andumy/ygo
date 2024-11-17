<?php

namespace App\Livewire;

use App\Models\CardInstance;
use App\Repositories\CardInstanceRepository;
use App\Repositories\SetRepository;
use App\Repositories\TradableCardRepository;
use Livewire\Component;

class TradableCards extends Component
{

    public string $set = '';
    public array $cardInstances = [];

    private CardInstanceRepository $cardInstanceRepository;
    private SetRepository $setRepository;
    private TradableCardRepository $tradableCardRepository;

    public function boot(
        CardInstanceRepository $cardInstanceRepository,
        SetRepository $setRepository,
        TradableCardRepository $tradableCardRepository,
    )
    {
        $this->cardInstanceRepository = $cardInstanceRepository;
        $this->setRepository = $setRepository;
        $this->tradableCardRepository = $tradableCardRepository;
    }
    public function refresh(){
        $this->cardInstances = [];
        foreach ($this->cardInstanceRepository->search(set: $this->set, ownedFilter: 1) as $ci){
            $this->cardInstances[] = [
                'id' => $ci->id,
                'card_set_code' => $ci->card_set_code,
                'card_name' => $ci->card->name,
                'total' => $ci->ownedCard?->amount,
                'collectable' => $ci->tradableCard?->collectable_amount ?? 0,
                'tradable' => $ci->tradableCard?->tradable_amount ?? 0,
            ];
        }
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
        }
    }

    public function render()
    {
        return view('livewire.tradable-cards', [
            'sets' => $this->setRepository->allWithCardsAndNewStock(),
        ]);
    }
}
