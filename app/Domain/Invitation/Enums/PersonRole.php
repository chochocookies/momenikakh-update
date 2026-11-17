<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Enums;

enum PersonRole: string
{
    case Groom = 'groom';
    case Bride = 'bride';
    case Parent = 'parent';
    case Baby = 'baby';
    case Child = 'child';
    case Celebrant = 'celebrant';
    case Host = 'host';
}
