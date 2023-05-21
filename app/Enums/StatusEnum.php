<?php

namespace App\Enums;

enum StatusEnum: string
{
    case New = 'новая';
    case InWork = 'в работе';
    case Ready = 'готово';
    case Rejected ='отклонено';
}


