<?php

declare(strict_types=1);

namespace App\Domain\Billing\Enums;

enum PlanTier: string
{
    case Free = 'free';
    case Premium = 'premium';
}
