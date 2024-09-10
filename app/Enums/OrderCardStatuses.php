<?php

namespace App\Enums;

enum OrderCardStatuses: string
{
    case ORDERED = 'ordered';
    case DELIVERED = 'delivered';
    case CANCELED = 'canceled';
}
