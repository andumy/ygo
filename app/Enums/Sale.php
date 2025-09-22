<?php

namespace App\Enums;

enum Sale: string
{
    case NOT_SET = 'NOT SET';
    case TRADE = 'TRADE';
    case IN_COLLECTION = 'IN COLLECTION';

    case LISTED = 'LISTED';
}
