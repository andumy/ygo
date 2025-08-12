<?php

namespace App\Enums;

enum Games: string
{
    case YGO = 'ygo';
    case MTG = 'mtg';
    case POKEMON = 'pokemon';
    case RIFTBOUND = 'riftbound';

    public function pretty(): string{
        return match ($this) {
            self::YGO => 'Yu-Gi-Oh!',
            self::MTG => 'Magic: The Gathering',
            self::POKEMON => 'Pokémon',
            self::RIFTBOUND => 'Riftbound',
        };
    }

    public function id(): string{
        return match ($this) {
            self::YGO => 1,
            self::MTG => 2,
            self::POKEMON => 3,
            self::RIFTBOUND => 4,
        };
    }
}
