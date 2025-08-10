<?php

namespace App\Livewire\Pages;

use App\Models\Card;
use Livewire\Component;

class AllVariantsForCard extends Component
{
    public Card $card;

    public function mount(Card $card)
    {
        $this->card = $card;
    }

    public function render()
    {
        return view('livewire.all-variants-for-card');
    }
}
