<?php

namespace App\Livewire;

use App\Models\Card;
use App\Models\VariantCard;
use Livewire\Component;

class SingleVariantCard extends Component
{
    public VariantCard $variantCard;

    public function mount(VariantCard $variantCard)
    {
        $this->variantCard = $variantCard;
    }

    public function render()
    {
        return view('livewire.single-variant-card');
    }
}
