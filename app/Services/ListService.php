<?php

namespace App\Services;

use App\Dtos\CatalogMatch;
use App\Models\OwnedCard;
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
        $ownedCards = $this->ownedCardRepository->getTradable();

        foreach ($ownedCards as $ownedCard) {
            $catalogMatches->push(
                $this->extractCatalogEntry($ownedCard)
            );
        }
        return $catalogMatches;
    }

    /** @param Collection<CatalogMatch> $catalogMatches */
    public function markOwnedCardsAsListed(Collection $catalogMatches): void
    {
        foreach ($catalogMatches as $catalogMatch) {
            $this->ownedCardRepository->markListed($catalogMatch->ownedCard);
        }
    }

    private function extractCatalogEntry(OwnedCard $ownedCard): CatalogMatch{
        $catalogs = $this->catalogRepository->search(
            expansionCode: $ownedCard->variant->cardInstance->card_set_code_base,
            number: $ownedCard->variant->cardInstance->card_set_code_nr,
            rarity: $ownedCard->variant->cardInstance->rarity_verbose,
            name: $ownedCard->variant->cardInstance->card->name,
        );

        if($catalogs->isEmpty()){
            $catalogs = $this->catalogRepository->search(
                expansionCode: $ownedCard->variant->cardInstance->card_set_code_base,
                number: $ownedCard->variant->cardInstance->card_set_code_nr,
                rarity: $ownedCard->variant->cardInstance->rarity_verbose,
            );
        }

        if($catalogs->isEmpty()){
            $catalogs = $this->catalogRepository->search(
                expansionCode: $ownedCard->variant->cardInstance->card_set_code_base,
                number: $ownedCard->variant->cardInstance->card_set_code_nr,
            );
        }

        if($catalogs->isEmpty()){
            $catalogs = $this->catalogRepository->search(
                number: $ownedCard->variant->cardInstance->card_set_code_nr,
                rarity: $ownedCard->variant->cardInstance->rarity_verbose,
                name: $ownedCard->variant->cardInstance->card->name,
            );
        }

        return new CatalogMatch(
            catalogs: $catalogs,
            ownedCard: $ownedCard,
            selectedCatalog: $catalogs->count() > 1 ? null : $catalogs->first()
        );
    }
}
