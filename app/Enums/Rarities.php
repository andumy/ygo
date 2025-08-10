<?php

namespace App\Enums;

enum Rarities: string
{
    case PROMO = "Promo";
    case MISSING = "";
    case NEW = "New";
    case COMMON = "Common";
    case REPRINT = "Reprint";
    case SHORT_PRINT = "Short Print";
    case SUPER_SHORT_PRINT = "Super Short Print";
    case EUROPEAN_OCEANIAN_DEBUT = "European & Oceanian debut";
    case EUROPEAN_DEBUT = "European debut";
    case OCEANIAN_DEBUT = "Oceanian debut";
    case RARE = "Rare";
    case SUPER_RARE = "Super Rare";
    case ULTRA_RARE = "Ultra Rare";
    case ULTRA_RARE_PHARAOHS_RARE = "Ultra Rare (Pharaoh's Rare)";
    case ULTRA_PHARAOHS_RARE = "Ultra Pharaoh’s Rare";
    case PLATINUM_RARE = "Platinum Rare";
    case GOLD_RARE = "Gold Rare";
    case PREMIUM_GOLD_RARE = "Premium Gold Rare";
    case SHATTERFOIL_RARE = "Shatterfoil Rare";
    case STARFOIL_RARE = "Starfoil Rare";
    case STARFOIL = "Starfoil";
    case STARLIGHT_RARE = "Starlight Rare";
    case MOSAIC_RARE = "Mosaic Rare";
    case SECRET_RARE = "Secret Rare";
    case ULTRA_SECRET_RARE = "Ultra Secret Rare";
    case EXTRA_SECRET_RARE = "Extra Secret Rare";
    case EXTRA_SECRET = "Extra Secret";
    case GOLD_SECRET_RARE = "Gold Secret Rare";
    case SECRET_PHARAOHS_RARE = "Secret Pharaoh’s Rare";
    case PRISMATIC_SECRET_RARE = "Prismatic Secret Rare";
    case PRISMATIC_ULTIMATE_RARE = "Prismatic Ultimate Rare";
    case COLLECTORS_RARE = "Collector's Rare";
    case PRISMATIC_COLLECTORS_RARE = "Prismatic Collector's Rare";
    case PLATINUM_SECRET_RARE = "Platinum Secret Rare";
    case ULTIMATE_RARE = "Ultimate Rare";
    case NORMAL_PARALLEL_RARE = "Normal Parallel Rare";
    case PARALLEL_RARE = "Parallel Rare";
    case SUPER_PARALLEL_RARE = "Super Parallel Rare";
    case ULTRA_PARALLEL_RARE = "Ultra Parallel Rare";
    case GHOST_GOLD_RARE = "Ghost/Gold Rare";
    case DUEL_TERMINAL_NORMAL_PARALLEL_RARE = "Duel Terminal Normal Parallel Rare";
    case DUEL_TERMINAL_NORMAL_RARE_PARALLEL_RARE = "Duel Terminal Normal Rare Parallel Rare";
    case DUEL_TERMINAL_RARE_PARALLEL_RARE = "Duel Terminal Rare Parallel Rare";
    case DUEL_TERMINAL_SUPER_PARALLEL_RARE = "Duel Terminal Super Parallel Rare";
    case DUEL_TERMINAL_ULTRA_PARALLEL_RARE = "Duel Terminal Ultra Parallel Rare";
    case QUARTER_CENTURY_SECRET_RARE = "Quarter Century Secret Rare";
    case TEN_THOUSANDS_SECRET_RARE = "10000 Secret Rare";
    case GHOST_RARE = "Ghost Rare";
    case RIFTBOUND_COMMON = "Riftbound Common";
    case RIFTBOUND_RARE = "Riftbound Rare";
    case RIFTBOUND_EPIC = "Riftbound Epic";
    case RIFTBOUND_MYTHIC = "Riftbound Mythic";
    case RIFTBOUND_LEGENDARY = "Riftbound Legendary";


    public function getShortHand(): ?string
    {
        return match ($this) {
            self::RIFTBOUND_COMMON => "⚪",
            self::RIFTBOUND_RARE => "🔵",
            self::RIFTBOUND_EPIC => "🟣",
            self::RIFTBOUND_MYTHIC => "🟠",
            self::RIFTBOUND_LEGENDARY => "🟡",
            default => null,
        };
    }

}
