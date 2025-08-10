<?php

namespace App\Livewire\Pages;

use App\Models\Card;
use App\Models\Set;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\SetRepository;
use App\Repositories\VariantCardRepository;
use App\Repositories\VariantRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class SetsAndInstances extends Component
{
    public string $message = '';

    public string $set_name = '';
    public string $set_code = '';
    public string $set_date = '';
    public int $set_amount = 0;

    public string $card_id = '';
    public string $set_id = '';
    public string $card_set_code = '';
    public string $rarity = '';


    public ?Card $card = null;
    public ?Set $set = null;

    public Collection $sets;
    public Collection $rarities;
    public bool $confirm = false;

    private SetRepository $setRepository;
    private CardRepository $cardRepository;
    private CardInstanceRepository $cardInstanceRepository;
    private VariantCardRepository $variantCardRepository;
    private VariantRepository $variantRepository;


    public function boot(
        SetRepository $setRepository,
        CardRepository $cardRepository,
        CardInstanceRepository $cardInstanceRepository,
        VariantCardRepository $variantCardRepository,
        VariantRepository $variantRepository
    )
    {
        $this->setRepository = $setRepository;
        $this->cardRepository = $cardRepository;
        $this->cardInstanceRepository = $cardInstanceRepository;
        $this->variantCardRepository = $variantCardRepository;
        $this->variantRepository = $variantRepository;
    }

    public function mount() {
        $this->sets = $this->setRepository->all();
        $this->rarities = $this->cardInstanceRepository->rarities();
    }

    private function resetFields()
    {
        $this->message = '';
        $this->set_name = '';
        $this->set_code = '';
        $this->set_date = '';
        $this->set_amount = 0;
        $this->card_id = '';
        $this->set_id = '';
        $this->card_set_code = '';
        $this->rarity = '';
        $this->card = null;
        $this->set = null;
        $this->confirm = false;
    }

    public function addSet(){
        $this->setRepository->firstOrCreate([
            'name' => $this->set_name,
            'code' => $this->set_code,
        ],[
            'date' => Carbon::parse($this->set_date)->format('Y-m-d'),
            'card_amount' => $this->set_amount,
        ]);
        $this->message = 'Set added successfully';
    }

    public function addInstance(){
        $this->card = $this->cardRepository->findByPasscode($this->card_id);

        if(!$this->card){
            $this->message = 'Card not found';
            return;
        }

        $this->set = $this->setRepository->findById($this->set_id);

        $this->confirm = true;
    }

    public function confirmSave()
    {
        $ci = $this->cardInstanceRepository->firstOrCreate([
            'card_id' => $this->card->id,
            'set_id' => $this->set->id,
            'card_set_code' => $this->card_set_code,
            'rarity_verbose' => $this->rarities[$this->rarity],
        ],[]);

        $variantCard = $this->variantCardRepository->findById($this->card_id);
        $this->variantRepository->firstOrCreate([
            'card_instance_id' => $ci->id,
            'variant_card_id' => $variantCard->id,
        ],[]);

        $this->message = $this->card->name . ' added to ' . $this->set->name .
            ' successfully as '. $this->rarity . ' with code '. $this->card_set_code;
    }

    public function render()
    {
        return view('livewire.sets-and-instances');
    }
}
