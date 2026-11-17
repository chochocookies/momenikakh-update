<?php

declare(strict_types=1);

namespace App\Domain\Billing\Models;

use App\Domain\Billing\Enums\PaymentStatus;
use App\Models\User;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    /** Provider callbacks must go through a service that verifies signatures; never mass-assign raw input. */
    protected $fillable = ['plan_id', 'subscription_id', 'order_number', 'provider', 'provider_reference', 'amount_idr', 'status', 'payload', 'paid_at'];

    protected static function newFactory(): PaymentFactory
    {
        return PaymentFactory::new();
    }

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount_idr' => 'integer',
            'payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
