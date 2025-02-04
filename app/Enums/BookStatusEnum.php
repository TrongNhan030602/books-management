<?php

namespace App\Enums;

enum BookStatusEnum: string
{
    case AVAILABLE = 'available';
    case BORROWED = 'borrowed';
    case RESERVED = 'reserved';
    case NOTAVAILABLE = 'not_available';
}
