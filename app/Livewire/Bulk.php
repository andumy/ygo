<?php

namespace App\Livewire;

use App\Enums\Condition;
use App\Enums\Lang;
use App\Enums\Rarities;
use App\Enums\Sale;
use App\Models\CardInstance;
use App\Repositories\CardInstanceRepository;
use App\Repositories\OwnedCardRepository;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use function array_unique;
use function count;
use function current;
use function dd;
use function mb_strtoupper;
use function storage_path;

class Bulk extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:xlsx')]
    public $file;
    public string $cellValue = '';
    public int $total = 0;

    public array $cards = [];
    public array $amounts = [];

    private CardInstanceRepository $cardInstanceRepository;
    private OwnedCardRepository $ownedCardRepository;

    public function boot(
        CardInstanceRepository $cardInstanceRepository,
        OwnedCardRepository $ownedCardRepository,
    )
    {
        $this->cardInstanceRepository = $cardInstanceRepository;
        $this->ownedCardRepository = $ownedCardRepository;
    }

    public function loadSheet()
    {
        $this->file->storeAs(path:'bulk', name: 'bulk.xlsx');
        $spreadsheet = IOFactory::load(storage_path('app/bulk/bulk.xlsx'));


        $index = 2;
        $lang = Lang::ENGLISH;
        $condition = Condition::NEAR_MINT;
        $rarity = Rarities::COMMON;
        $set = '';
        $version = 2;

        do{
            $lang = $spreadsheet->getActiveSheet()->getCell("C$index")->getValue() ?? $lang->value;
            $set = $spreadsheet->getActiveSheet()->getCell("A$index")->getValue() ?? $set;
            $version = $spreadsheet->getActiveSheet()->getCell("B$index")->getValue() ?? $version;
            $cardIndex = $spreadsheet->getActiveSheet()->getCell("D$index")->getValue();
            $amount = $spreadsheet->getActiveSheet()->getCell("E$index")->getValue();
            $isFirstEdition = $spreadsheet->getActiveSheet()->getCell("F$index")->getValue() != '';
            $rarity = $spreadsheet->getActiveSheet()->getCell("G$index")->getValue();
            $condition = $spreadsheet->getActiveSheet()->getCell("H$index")->getValue() ?? $condition->getShortHand();
            $index++;

            if(!$cardIndex){
                break;
            }

            $code = mb_strtoupper($set) .
                ($version == 2 ? '-EN' : ($version == 1 ? '-E' : '-')) .
                mb_strtoupper($cardIndex);

            $lang = Lang::from(mb_strtoupper($lang));
            $condition = Condition::revertShortHand(mb_strtoupper($condition));

            if(!$amount){
                continue;
            }

            if($rarity != ''){
                $rarity = Rarities::from($rarity);
                $cardInstances = $this->cardInstanceRepository->findBySetCodeAndRarity($code, $rarity);
            } else {
                $rarity = Rarities::MISSING;
                $cardInstances = $this->cardInstanceRepository->findBySetCode($code);
            }

            $currentCard = $this->cards
            [$code]
            [$lang->value]
            [$condition->value]
            [$isFirstEdition]
            [$rarity->value] ?? [];

            $this->cards
            [$code]
            [$lang->value]
            [$condition->value]
            [$isFirstEdition]
            [$rarity->value] = [
                'amount' => ($currentCard['amount'] ?? 0) + $amount,
                'cardInstance' => $cardInstances,
            ];

            $this->total += $amount;

        } while (1);

        foreach ($this->cards as $code => $codeArray){
            foreach ($codeArray as $lang => $langArray){
                foreach ($langArray as $condition => $conditionArray){
                    foreach ($conditionArray as $isFirstEdition => $isFirstEditionArray){
                        foreach ($isFirstEditionArray as $rarity => $cardObject) {

                            if($cardObject['cardInstance']->count() == 0) {
                                dd($code);
                            }

                            $this->amounts
                            [$code]
                            [$lang]
                            [$condition]
                            [$isFirstEdition]
                            [$rarity]
                            [$cardObject['cardInstance']->first()->id] = $cardObject['amount'];
                        }
                    }
                }
            }
        }

        Storage::deleteDirectory('livewire-tmp');
        Storage::deleteDirectory('bulk');
    }

    public function save(){
        $failedCards = [];
        $batch = $this->ownedCardRepository->fetchNextBatch();

        foreach ($this->cards as $code => $codeArray){
            foreach ($codeArray as $lang => $langArray){
                foreach ($langArray as $condition => $conditionArray){
                    foreach ($conditionArray as $isFirstEdition => $isFirstEditionArray){
                        foreach ($isFirstEditionArray as $rarity => $cardObject) {
                            /** @var Collection<CardInstance> $cardInstances */
                            $cardInstances = $cardObject['cardInstance'];
                            if ($cardInstances->count() > 1) {

                                $sum = 0;
                                foreach ($this->amounts[$code][$lang][$condition][$isFirstEdition][$rarity] ?? [] as $cost) {
                                    $sum += (int)$cost;
                                }

                                if ($sum != $cardObject['amount']) {
                                    $failedCards[$code][$lang][$condition][$isFirstEdition][$rarity] = $cardObject;
                                    continue;
                                }

                                foreach ($cardInstances as $cardInstance) {
                                    $amount = $this->amounts[$code][$lang][$condition][$isFirstEdition][$rarity][$cardInstance->id] ?? 0;
                                    if (!$amount) {
                                        continue;
                                    }

                                    $this->ownedCardRepository->createAmount(
                                        cardInstanceId: $cardInstance->id,
                                        batch: $batch,
                                        amount: $amount,
                                        lang: Lang::from($lang),
                                        condition: Condition::from($condition),
                                        isFirstEdition: (bool)$isFirstEdition,
                                    );
                                }
                            } else {
                                $this->ownedCardRepository->createAmount(
                                    cardInstanceId: $cardInstances->first()->id,
                                    batch: $batch,
                                    amount: $cardObject['amount'],
                                    lang: Lang::from($lang),
                                    condition: Condition::from($condition),
                                    isFirstEdition: (bool)$isFirstEdition,
                                );
                            }
                        }
                    }
                }
            }
        }

        $this->cards = $failedCards;
    }
    public function render()
    {
        return view('livewire.bulk');
    }
}
