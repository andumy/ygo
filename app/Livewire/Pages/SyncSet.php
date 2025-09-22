<?php

namespace App\Livewire\Pages;

use App\Models\Card;
use App\Repositories\CardInstanceRepository;
use App\Repositories\CardRepository;
use App\Repositories\SetRepository;
use App\Repositories\VariantCardRepository;
use App\Repositories\VariantRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use function count;
use function json_decode;

class SyncSet extends Component
{
    public int $setId;
    public Collection $sets;
    public string $data;
    public string $message = '';
    public array $passCodes = [];

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
        $this->cardRepository = $cardRepository;
        $this->cardInstanceRepository = $cardInstanceRepository;
        $this->variantCardRepository = $variantCardRepository;
        $this->variantRepository = $variantRepository;

        $this->sets = $setRepository->all();
    }

    public function fetch() {
        $parsedData = json_decode($this->data);

        foreach ($parsedData as $entry) {
            $card = $this->cardRepository->findByName($entry->name);
            if (!$card) {
                $this->passCodes[$entry->name] = null;
            }
        }

        if(empty($this->passCodes)) {
            $this->injectData();
        }
    }

    public function injectData() {
        foreach ($this->passCodes as $passCode) {
            if($passCode === null) {
                $this->message = 'Some cards were not found. Please provide passcodes for the missing cards.';
                return;
            }
        }
        $parsedData = json_decode($this->data);
        foreach ($parsedData as $entry) {
            /** @var object{code: string, name: string, rarity: string} $entry */
            $variantCard = null;
            /** @var Card $card */
            $card = $this->cardRepository->findByName($entry->name);
            if (!$card) {
                $card = $this->cardRepository->findByPasscode($this->passCodes[$entry->name]);
                if(!$card) {
                    $card = $this->cardRepository->firstOrCreateWithGame([
                        'name' => $entry->name,
                        'game_id' => Session::get('game_id'),
                    ],[]);
                    $variantCard = $this->variantCardRepository->create($this->passCodes[$entry->name], true);
                }
            }
            $ci = $this->cardInstanceRepository->firstOrCreate([
                'card_id' => $card->id,
                'set_id' => $this->setId,
                'card_set_code' => $entry->code,
                'rarity_verbose' => $entry->rarity,
            ],[]);

            if(!$variantCard) {
                $variantCard = $this->variantCardRepository->findById($card->passcode);
            }
            $this->variantRepository->firstOrCreate([
                'card_instance_id' => $ci->id,
                'variant_card_id' => $variantCard->id,
            ],[]);
        }

        $this->message = count($parsedData).' cards successfully synced.';
        $this->data = '';
        $this->passCodes = [];
    }

    public function render()
    {
        return view('livewire.sync-set');
    }
}
