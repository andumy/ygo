<?php

namespace App\Synths;

use App\Dtos\CatalogMatch;
use App\Models\Card;
use App\Models\CardInstance;
use App\Models\Catalog;
use App\Models\OwnedCard;
use App\Models\Set;
use App\Models\Variant;
use Illuminate\Support\Collection;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class CatalogMatchSynth extends Synth
{
    public static string $key = 'catalog_match';

    public static function match(mixed $target)
    {
        return $target instanceof CatalogMatch;
    }

    public function dehydrate(CatalogMatch $target)
    {
        return [[
            'catalogs' => $target->catalogs,
            'ownedCard' => $target->ownedCard,
            'selectedCatalog' => $target->selectedCatalog,
        ], []];
    }

    public function hydrate(array $array)
    {
        $catalogs = new Collection();
        foreach ($array['catalogs'] as $catalog) {
            $catalogs->push(
                (new Catalog())->fill($catalog)
            );
        }

        $ownedCard = new OwnedCard();
        $ownedCard->fill($array['ownedCard']);

        if (isset($array['ownedCard']['variant'])) {
            $variant = new Variant();
            $variant->fill($array['ownedCard']['variant']);

            if (isset($array['ownedCard']['variant']['cardInstance'])) {
                $cardInstance = new CardInstance();
                $cardInstance->fill($array['ownedCard']['variant']['cardInstance']);

                if (isset($array['ownedCard']['variant']['cardInstance']['card'])) {
                    $card = new Card();
                    $card->fill($array['ownedCard']['variant']['cardInstance']['card']);

                    unset($cardInstance->card);
                    $cardInstance->setRelation('card', $card);
                }

                if (isset($array['ownedCard']['variant']['cardInstance']['set'])) {
                    $set = new Set();
                    $set->fill($array['ownedCard']['variant']['cardInstance']['set']);

                    unset($cardInstance->set);
                    $cardInstance->setRelation('set', $set);
                }

                unset($variant->cardInstance);
                $variant->setRelation('cardInstance', $cardInstance);
            }

            unset($ownedCard->variant);
            $ownedCard->setRelation('variant', $variant);
        }

        return new CatalogMatch(
            catalogs: $catalogs,
            ownedCard: $ownedCard,
            selectedCatalog: $array['selectedCatalog'] ? (new Catalog())->fill($array['selectedCatalog']) : null
        );
    }
}
