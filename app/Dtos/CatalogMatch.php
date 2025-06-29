<?php

namespace App\Dtos;

use App\Models\Catalog;
use App\Models\OwnedCardWithAmount;
use Illuminate\Support\Collection;

class CatalogMatch
{

    /**
     * @param Collection<Catalog> $catalogs
     * @param OwnedCardWithAmount $ownedCardWithAmount
     * @param Catalog|null $selectedCatalog
     */
    public function __construct(
        public Collection          $catalogs,
        public OwnedCardWithAmount $ownedCardWithAmount,
        public ?Catalog            $selectedCatalog = null
    )
    {
    }

}
