<?php

namespace App\Enums;

use function asset;

enum Sale: string
{
    case NOT_SET = 'NOT SET';
    case TRADE = 'TRADE';
    case IN_COLLECTION = 'IN COLLECTION';
}
