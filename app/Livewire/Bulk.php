<?php

namespace App\Livewire;

use App\Enums\Condition;
use App\Enums\Lang;
use App\Enums\Sale;
use App\Models\CardInstance;
use App\Repositories\CardInstanceRepository;
use App\Repositories\OwnedCardRepository;
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
use function storage_path;

class Bulk extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:xlsx')]
    public $file;
    public string $cellValue = '';

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
        do{
            $lang = $spreadsheet->getActiveSheet()->getCell("B$index")->getValue();
            $code = $spreadsheet->getActiveSheet()->getCell("A$index")->getValue() .
                $spreadsheet->getActiveSheet()->getCell("C$index")->getValue();
            $amount = $spreadsheet->getActiveSheet()->getCell("D$index")->getValue();
            $isFirstEdition = $spreadsheet->getActiveSheet()->getCell("E$index")->getValue() != '';
            $condition = $spreadsheet->getActiveSheet()->getCell("F$index")->getValue();

            if(!$code){
                break;
            }

            $lang = Lang::from($lang);
            $condition = Condition::revertShortHand($condition);

            $cardInstances = $this->cardInstanceRepository->findBySetCode($code);

            $currentCard = $this->cards
            [$code]
            [$lang->value]
            [$condition->value]
            [$isFirstEdition] ?? [];

            $this->cards
            [$code]
            [$lang->value]
            [$condition->value]
            [$isFirstEdition] = [
                'amount' => ($currentCard['amount'] ?? 0) + $amount,
                'cardInstance' => $cardInstances,
            ];

            $index++;
        } while (1);

        Storage::deleteDirectory('livewire-tmp');
        Storage::deleteDirectory('bulk');
    }

    public function save(){
        $failedCards = [];
        $batch = $this->ownedCardRepository->fetchNextBatch();

        foreach ($this->cards as $code => $codeArray){
            foreach ($codeArray as $lang => $langArray){
                foreach ($langArray as $condition => $conditionArray){
                    foreach ($conditionArray as $isFirstEdition => $cardObject){
                        /** @var Collection<CardInstance> $cardInstances */
                        $cardInstances = $cardObject['cardInstance'];
                        if($cardInstances->count() > 1) {

                            $sum = 0;
                            foreach ($this->amounts[$code][$lang][$condition][$isFirstEdition] ?? [] as $cost){
                                $sum += (int)$cost;
                            }

                            if($sum != $cardObject['amount']){
                                $failedCards[$code][$lang][$condition][$isFirstEdition] = $cardObject;
                                continue;
                            }

                            foreach ($cardInstances as $cardInstance){
                                $amount = $this->amounts[$code][$lang][$condition][$isFirstEdition][$cardInstance->id] ?? 0;
                                if(!$amount){
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

        $this->cards = $failedCards;
    }
    public function render()
    {
        return view('livewire.bulk');
    }
}
