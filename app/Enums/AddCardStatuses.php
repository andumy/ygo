<?php

namespace App\Enums;

enum AddCardStatuses: int
{
    case NOT_FOUND = 0;
    case INCREMENT = 1;
    case NEW_CARD = 2;
    case MULTIPLE_OPTIONS = 3;
    case PART_OF_ANOTHER_ORDER = 5;
}
