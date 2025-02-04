<?php

namespace App\Enums;

use function asset;

enum Lang: string
{
    case ENGLISH = 'EN';
    case FRENCH = 'FR';
    case GERMAN = 'DE';
    case ITALIAN = 'IT';
    case PORTUGUESE = 'PT';
    case SPANISH = 'SP';
    case JAPANESE = 'JP';
    case JAPANESE_ASIAN = 'JA';
    case ASIAN_ENGLISH = 'AE';
    case KOREAN = 'KR';
    case TRADITIONAL_CHINESE = 'TC';
    case SIMPLIFIED_CHINESE = 'SC';

    /**
     * @return string
     */
    public function getFlag(): string
    {
        return asset('storage/lang/'. $this->value . '.jpg');
    }
}
