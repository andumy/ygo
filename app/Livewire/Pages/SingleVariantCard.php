<?php

namespace App\Livewire\Pages;

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
