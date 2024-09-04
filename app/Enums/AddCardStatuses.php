<?php

namespace App\Enums;

enum AddCardStatuses: int
{
    case NOT_FOUND = 0;
    case INCREMENT = 1;
    case NEW_CARD = 2;
    case MULTIPLE_OPTIONS = 3;
}
