<?php

namespace App\Services;

use App\Dtos\CatalogMatch;
use App\Models\Catalog;
use App\Models\OwnedCardWithAmount;
use App\Models\Variant;
use App\Repositories\CatalogRepository;
use App\Repositories\OwnedCardRepository;
use Illuminate\Support\Collection;

class ListService
{
    public function __construct(
        private readonly OwnedCardRepository $ownedCardRepository,
        private readonly CatalogRepository $catalogRepository,
    )
    {
    }


    /**
     * @return Collection<CatalogMatch>
     */
    public function generateList(): Collection{
        $catalogMatches = new Collection();
        $ownedCardsWithAmount = $this->ownedCardRepository->getTradable();

        foreach ($ownedCardsWithAmount as $ownedCardWithAmount) {
            $catalogMatches->push(
                $this->extractCatalogEntry($ownedCardWithAmount)
            );
        }
        return $catalogMatches;
    }

    /** @param Collection<CatalogMatch> $catalogMatches */
    public function markOwnedCardsAsListed(Collection $catalogMatches): void
    {
        foreach ($catalogMatches as $catalogMatch) {
            $this->ownedCardRepository->markListed($catalogMatch->ownedCardWithAmount);
        }
    }

    private function extractCatalogEntry(OwnedCardWithAmount $ownedCardWithAmount): CatalogMatch{

        $catalogs = $this->getCatalogsMatch($ownedCardWithAmount->variant);
        return new CatalogMatch(
            catalogs: $catalogs,
            ownedCardWithAmount: $ownedCardWithAmount,
            selectedCatalog: $catalogs->count() > 1 ? null : $catalogs->first()
        );
    }

    /** @return Collection<Catalog> */
    public function getCatalogsMatch(Variant $variant): Collection{
        $catalogs = $this->catalogRepository->search(
            expansionCode: $variant->cardInstance->card_set_code_base,
            number: $variant->cardInstance->card_set_code_nr,
            rarity: $variant->cardInstance->rarity_verbose,
            name: $variant->cardInstance->card->name,
        );

        if(!$catalogs->isEmpty()){
            return $catalogs;
        }

        $catalogs = $this->catalogRepository->search(
            expansionCode: $variant->cardInstance->card_set_code_base,
            number: $variant->cardInstance->card_set_code_nr,
            rarity: $variant->cardInstance->rarity_verbose,
        );
        if(!$catalogs->isEmpty()){
            return $catalogs;
        }

        $catalogs = $this->catalogRepository->search(
            expansionCode: $variant->cardInstance->card_set_code_base,
            number: $variant->cardInstance->card_set_code_nr,
        );
        if(!$catalogs->isEmpty()){
            return $catalogs;
        }

        return $this->catalogRepository->search(
            number: $variant->cardInstance->card_set_code_nr,
            rarity: $variant->cardInstance->rarity_verbose,
            name: $variant->cardInstance->card->name,
        );
    }
}
