<?php

declare(strict_types=1);

namespace App\Domain\Billing;

/** Indonesian rupiah text: "Rp 50.000". Amounts are whole rupiah (integers) everywhere in the app. */
final class Money
{
    public static function rupiah(int $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
