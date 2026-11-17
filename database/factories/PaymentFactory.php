<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Billing\Enums\PaymentStatus;
use App\Domain\Billing\Models\Payment;
use App\Domain\Billing\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'order_number' => 'MN-'.strtoupper(Str::random(10)),
            'provider' => 'duitku',
            'amount_idr' => 50000,           // test value only; real prices come from DEC-01
            'status' => PaymentStatus::Pending,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => PaymentStatus::Paid, 'paid_at' => now(), 'provider_reference' => 'REF-'.strtoupper(Str::random(8))]);
    }
}
