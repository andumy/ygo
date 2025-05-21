<?php

namespace App\Jobs;

use App\Repositories\CatalogRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use function storage_path;

class ProcessCatalog implements ShouldQueue
{
    use Queueable;


    /**
     * Create the event listener.
     */
    public function __construct(private readonly array $lines)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(CatalogRepository $catalogRepository): void
    {
        $start = $this->lines['start'];
        $end = $this->lines['end'];

        $handle = fopen(storage_path('app/catalog/catalog.csv'), 'r');

        if (!$handle) {
            throw new \Exception('Unable to open CSV file.');
        }

        $line = 0;

        while (($row = fgetcsv($handle, 0)) !== false) {
            $line++;

            // Skip lines before the desired start
            if ($line < $start) {
                continue;
            }

            // Stop if we've gone past the desired end
            if ($line > $end) {
                break;
            }

            // Ensure there are enough columns
            if (count($row) < 6) {
                continue;
            }

            [$cardMarketId, $name, $number, $rarity, $expansion, $expansionCode] = $row;

            if (!$cardMarketId) {
                break; // End of relevant data
            }

            $catalogRepository->create(
                name: $name,
                cardMarketId: $cardMarketId,
                number: $number,
                rarity: $rarity,
                expansion: $expansion,
                expansionCode: $expansionCode
            );
        }

        fclose($handle);
    }
}
