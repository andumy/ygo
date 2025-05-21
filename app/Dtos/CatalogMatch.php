<?php

namespace App\Dtos;

use App\Models\Catalog;
use App\Models\OwnedCard;
use Illuminate\Support\Collection;

class CatalogMatch
{

    /**
     * @param Collection<Catalog> $catalogs
     * @param OwnedCard $ownedCard
     * @param Catalog|null $selectedCatalog
     */
    public function __construct(
        public Collection         $catalogs,
        public OwnedCard        $ownedCard,
        public ?Catalog $selectedCatalog = null
    )
    {
    }

}
