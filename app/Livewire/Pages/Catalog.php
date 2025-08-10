<?php

namespace App\Livewire\Pages;

use App\Dtos\CatalogMatch;
use App\Jobs\ProcessCatalog;
use App\Repositories\CatalogRepository;
use App\Services\ListService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use function fclose;
use function feof;
use function fgets;
use function fopen;
use function fputcsv;
use function storage_path;

class Catalog extends Component
{
    use WithFileUploads;

    private const CHUNK_SIZE = 5000;

    #[Validate('required|file|mimes:xlsx')]
    public $file;
    public string $message;

    public int $total = 0;


    /** @var Collection<CatalogMatch> $catalogMatches */
    public Collection $catalogMatches;
    private CatalogRepository $catalogRepository;
    private ListService $listService;

    public function boot(
        CatalogRepository $catalogRepository,
        ListService $listService
    )
    {
        $this->catalogRepository = $catalogRepository;
        $this->listService = $listService;
    }

    public function loadCatalog()
    {
        $this->catalogRepository->truncate();

        $this->file->storeAs(path:'catalog', name: 'catalog.csv');

        $maxLines = $this->countCsvLines(storage_path('app/catalog/catalog.csv'));
        $start = 2;
        while($start < $maxLines) {
            $end = $start + self::CHUNK_SIZE;
            if($end > $maxLines) {
                $end = $maxLines;
            }
            ProcessCatalog::dispatch([
                'start' => $start,
                'end' => $end,
            ])->onQueue('database');
            $start = $end + 1;
        }

        $this->message = 'Catalog loaded successfully';
    }


    public function generateListing(): void
    {
        $this->total = 0;
        $this->catalogMatches = $this->listService->generateList();

        foreach ($this->catalogMatches as $catalogMatch) {
            $this->total += $catalogMatch->ownedCardWithAmount->amount;
        }
    }

    public function exportListing(): void
    {

        $path = storage_path('app/catalog/listing.csv');

        $handle = fopen($path, 'a');

        if (!$handle) {
            throw new \Exception("Unable to create file at {$path}");
        }


        foreach ($this->catalogMatches as $catalogMatch) {
            /** @var \App\Models\Catalog $catalog */
            $catalog = $catalogMatch->selectedCatalog;

            fputcsv($handle, [
                $catalog->cardmarket_id,
                $catalogMatch->ownedCardWithAmount->amount,
                $catalog->name,
                $catalog->expansion,
                $catalogMatch->ownedCardWithAmount->is_first_edition ? '1' : '0',
                $catalogMatch->ownedCardWithAmount->cond->getShortHand(),
                $catalogMatch->ownedCardWithAmount->lang->getLongName()
            ]);
        }

        fclose($handle);

        $this->listService->markOwnedCardsAsListed($this->catalogMatches);

        $this->generateListing();
    }

    public function selectCatalog(int $index, int $cardMarketId): void
    {
        $this->catalogMatches[$index]->selectedCatalog = $this->catalogMatches[$index]->catalogs->firstWhere('cardmarket_id', $cardMarketId);
    }

    private function countCsvLines($filePath) {
        $lineCount = 0;
        $handle = fopen($filePath, "r");
        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line !== false) {
                $lineCount++;
            }
        }
        fclose($handle);
        return $lineCount;
    }

    public function render()
    {
        return view('livewire.catalog');
    }
}
