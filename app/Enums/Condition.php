<?php

namespace App\Enums;

enum Condition: string
{
    case MINT = 'MINT';
    case NEAR_MINT = 'NEAR MINT';
    case EXCELLENT = 'EXCELLENT';
    case GOOD = 'GOOD';
    case LIGHT_PLAYED = 'LIGHT PLAYED';
    case PLAYED = 'PLAYED';
    case POOR = 'POOR';

    public static function revertShortHand(string $shortHand): self
    {
        return match ($shortHand) {
            'MT' => self::MINT,
            'NM' => self::NEAR_MINT,
            'EX' => self::EXCELLENT,
            'GD' => self::GOOD,
            'LP' => self::LIGHT_PLAYED,
            'PL' => self::PLAYED,
            'PO' => self::POOR,
        };
    }

    public function getShortHand(): string
    {
        return match ($this) {
            self::MINT => "<p class='text-center p-1 text-xs bg-sky-600 rounded-md text-stone-50'>MT</p>",
            self::NEAR_MINT => "<p class='text-center p-1 text-xs bg-teal-400 rounded-md text-stone-950'>NM</p>",
            self::EXCELLENT => "<p class='text-center p-1 text-xs bg-green-600 rounded-md text-stone-950'>EX</p>",
            self::GOOD => "<p class='text-center p-1 text-xs bg-amber-400 rounded-md text-stone-950'>GD</p>",
            self::LIGHT_PLAYED => "<p class='text-center p-1 text-xs bg-orange-400 rounded-md text-stone-950'>LP</p>",
            self::PLAYED => "<p class='text-center p-1 text-xs bg-orange-700 rounded-md text-stone-950'>PL</p>",
            self::POOR => "<p class='text-center p-1 text-xs bg-red-900 rounded-md text-stone-50'>PO</p>",
        };
    }
}
