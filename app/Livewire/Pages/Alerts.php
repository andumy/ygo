<?php

namespace App\Livewire\Pages;

use App\Repositories\VariantRepository;
use Livewire\Component;

class Alerts extends Component
{
    private VariantRepository $variantRepository;
    public function boot(VariantRepository $variantRepository)
    {
        $this->variantRepository = $variantRepository;
    }

    public function render()
    {
        $tradedNotCollected = $this->variantRepository->getAllTradableNotCollected();
        return view('livewire.alerts', [
            'tradedNotCollected' => $tradedNotCollected,
        ]);
    }
}
