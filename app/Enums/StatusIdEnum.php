<?php

namespace App\Enums;

enum StatusIdEnum: int
{
    case New = 1;
    case InWork = 2;
    case Ready = 3;
    case Rejected = 4;
}


