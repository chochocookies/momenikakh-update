<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Enums;

enum GiftType: string
{
    case Bank = 'bank';
    case Ewallet = 'ewallet';
    case Address = 'address';
}
