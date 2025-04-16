<?php

namespace App\Livewire;

use App\Models\Variant;
use Livewire\Component;

class SingleVariant extends Component
{
    public Variant $variant;

    public function mount(Variant $variant)
    {
        $this->variant = $variant;
    }

    public function render()
    {
        return view('livewire.single-variant');
    }
}
