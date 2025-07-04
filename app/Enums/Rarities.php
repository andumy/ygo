<?php

namespace App\Enums;

use function Laravel\Prompts\search;

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


    public function getShortHand(): string
    {
        return match ($this) {
            self::COMMON => "C",
            self::QUARTER_CENTURY_SECRET_RARE => "QCSR",
            self::ULTRA_RARE => "UR",
            self::SUPER_RARE => "SR",
            self::RARE => "R",
            self::SHORT_PRINT => "SP",
            self::SHATTERFOIL_RARE => "SHR",
            self::DUEL_TERMINAL_NORMAL_PARALLEL_RARE => "DTNPR",
            self::ULTIMATE_RARE => "ULTR",
            self::SECRET_RARE => "SCR",
            self::MOSAIC_RARE => "MSR",
            self::GOLD_SECRET_RARE => "GSR",
            self::GOLD_RARE => "GR",
            self::STARFOIL_RARE => "SFR",
            self::COLLECTORS_RARE => "CR",
            self::STARLIGHT_RARE => "SLR",
            self::PREMIUM_GOLD_RARE => "PGR",
            self::PRISMATIC_SECRET_RARE => "PSR",
            self::STARFOIL => "SF",
            self::DUEL_TERMINAL_RARE_PARALLEL_RARE => "DTRPR",
            self::DUEL_TERMINAL_SUPER_PARALLEL_RARE => "DTSPR",
            self::DUEL_TERMINAL_ULTRA_PARALLEL_RARE => "DTUPR",
            self::EXTRA_SECRET_RARE => "ESCR",
            self::GHOST_RARE => "GHR",
            self::REPRINT => "RP",
            self::NORMAL_PARALLEL_RARE => "NPR",
            self::PLATINUM_RARE => "PLR",
            self::ULTRA_PARALLEL_RARE => "ULPR",
            self::NEW => "N",
            self::ULTRA_RARE_PHARAOHS_RARE => "URPR",
            self::GHOST_GOLD_RARE => "GHGR",
            self::PLATINUM_SECRET_RARE => "PLSR",
            self::EUROPEAN_OCEANIAN_DEBUT => "EOD",
            self::SUPER_SHORT_PRINT => "SSP",
            self::SUPER_PARALLEL_RARE => "SPR",
            self::ULTRA_SECRET_RARE => "USR",
            self::EXTRA_SECRET => "ES",
            self::EUROPEAN_DEBUT => "ED",
            self::TEN_THOUSANDS_SECRET_RARE => "10KSR",
            self::OCEANIAN_DEBUT => "OD",
            self::PRISMATIC_COLLECTORS_RARE => "PCR",
            self::PRISMATIC_ULTIMATE_RARE => "PUR",
            self::PROMO => "P",
            self::PARALLEL_RARE => "PR",
            self::ULTRA_PHARAOHS_RARE => "UPR",
            self::SECRET_PHARAOHS_RARE => "SPHR",
            self::DUEL_TERMINAL_NORMAL_RARE_PARALLEL_RARE => "DTNRPR",
            default => "N/A",
        };
    }

}
