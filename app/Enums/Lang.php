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

    public function getLongName(): string{
        return match ($this) {
            self::ENGLISH => 'English',
            self::FRENCH => 'French',
            self::GERMAN => 'German',
            self::ITALIAN => 'Italian',
            self::PORTUGUESE => 'Portuguese',
            self::SPANISH => 'Spanish',
            self::JAPANESE => 'Japanese',
            self::JAPANESE_ASIAN => 'Japanese (Asian)',
            self::ASIAN_ENGLISH => 'Asian English',
            self::KOREAN => 'Korean',
            self::TRADITIONAL_CHINESE => 'Traditional Chinese',
            self::SIMPLIFIED_CHINESE => 'Simplified Chinese'
        };
    }
}
