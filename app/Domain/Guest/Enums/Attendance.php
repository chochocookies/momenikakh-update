<?php

declare(strict_types=1);

namespace App\Domain\Guest\Enums;

enum Attendance: string
{
    case Yes = 'yes';
    case No = 'no';
    case Maybe = 'maybe';
}
