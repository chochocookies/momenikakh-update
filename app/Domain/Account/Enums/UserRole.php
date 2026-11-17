<?php

declare(strict_types=1);

namespace App\Domain\Account\Enums;

enum UserRole: string
{
    case User = 'user';
    case Admin = 'admin';
}
