<?php

namespace App\Livewire\Pages;

use App\Enums\Condition;
use App\Enums\Lang;
use App\Enums\Rarities;
use App\Models\Variant;
use App\Repositories\OwnedCardRepository;
use App\Repositories\VariantRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use function dd;
use function mb_strtoupper;
use function storage_path;

class Sell extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:xlsx')]
    public $file;
    public string $cellValue = '';
    public int $total = 0;

    public string $message = '';

    public array $cards = [];
    public array $amounts = [];

    private OwnedCardRepository $ownedCardRepository;
    private VariantRepository $variantRepository;

    public function boot(
        OwnedCardRepository $ownedCardRepository,
        VariantRepository $variantRepository,
    )
    {
        $this->ownedCardRepository = $ownedCardRepository;
        $this->variantRepository = $variantRepository;
    }

    public function loadSheet()
    {
        $this->file->storeAs(path:'bulk', name: 'bulk.xlsx');
        $spreadsheet = IOFactory::load(storage_path('app/bulk/bulk.xlsx'));


        $index = 2;
        $lang = Lang::ENGLISH;
        $condition = Condition::NEAR_MINT;
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
                $variants = $this->variantRepository->getBySetCodeAndRarity($code, $rarity, true);
            } else {
                $rarity = Rarities::MISSING;
                $variants = $this->variantRepository->getBySetCode($code, true);
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
                'variants' => $variants,
            ];

            $this->total += $amount;

        } while (1);

        foreach ($this->cards as $code => $codeArray){
            foreach ($codeArray as $lang => $langArray){
                foreach ($langArray as $condition => $conditionArray){
                    foreach ($conditionArray as $isFirstEdition => $isFirstEditionArray){
                        foreach ($isFirstEditionArray as $rarity => $cardObject) {

                            if($cardObject['variants']->count() == 0) {
                                dd($code);
                            }

                            $this->amounts
                            [$code]
                            [$lang]
                            [$condition]
                            [$isFirstEdition]
                            [$rarity]
                            [$cardObject['variants']->first()->id] = $cardObject['amount'];
                        }
                    }
                }
            }
        }

        Storage::deleteDirectory('livewire-tmp');
        Storage::deleteDirectory('bulk');
    }

    public function purgeSell(){
        $this->ownedCardRepository->purgeListed();
        $this->message = 'Sold cards purged';
    }

    public function save(){
        $failedCards = [];

        foreach ($this->cards as $code => $codeArray){
            foreach ($codeArray as $lang => $langArray){
                foreach ($langArray as $condition => $conditionArray){
                    foreach ($conditionArray as $isFirstEdition => $isFirstEditionArray){
                        foreach ($isFirstEditionArray as $rarity => $cardObject) {
                            /** @var Collection<Variant> $variants */
                            $variants = $cardObject['variants'];
                            if ($variants->count() > 1) {

                                $sum = 0;
                                foreach ($this->amounts[$code][$lang][$condition][$isFirstEdition][$rarity] ?? [] as $cost) {
                                    $sum += (int)$cost;
                                }

                                if ($sum != $cardObject['amount']) {
                                    $failedCards[$code][$lang][$condition][$isFirstEdition][$rarity] = $cardObject;
                                    continue;
                                }

                                foreach ($variants as $variant) {
                                    $amount = $this->amounts[$code][$lang][$condition][$isFirstEdition][$rarity][$variant->id] ?? 0;
                                    if (!$amount) {
                                        continue;
                                    }

                                    $totalSold = $this->ownedCardRepository->sellAmount(
                                        variantId: $variant->id,
                                        amount: $amount,
                                        lang: Lang::from($lang),
                                        condition: Condition::from($condition),
                                        isFirstEdition: (bool)$isFirstEdition,
                                    );

                                    if($amount != $totalSold){
                                        $this->amounts[$code][$lang][$condition][$isFirstEdition][$rarity][$variant->id] -= $totalSold;
                                        $failedCards[$code][$lang][$condition][$isFirstEdition][$rarity] = $cardObject;
                                    }
                                }
                            } else {
                                $totalSold = $this->ownedCardRepository->sellAmount(
                                    variantId: $variants->first()->id,
                                    amount: $cardObject['amount'],
                                    lang: Lang::from($lang),
                                    condition: Condition::from($condition),
                                    isFirstEdition: (bool)$isFirstEdition,
                                );
                                if($cardObject['amount'] != $totalSold){
                                    $cardObject['amount'] -= $totalSold;
                                    $failedCards[$code][$lang][$condition][$isFirstEdition][$rarity] = $cardObject;
                                }
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
        return view('livewire.sell');
    }
}
