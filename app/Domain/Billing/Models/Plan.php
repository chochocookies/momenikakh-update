<?php

declare(strict_types=1);

namespace App\Domain\Billing\Models;

use App\Domain\Billing\Enums\PlanTier;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';

    protected $fillable = ['key', 'name', 'tier', 'price_idr', 'duration_days', 'features', 'limits', 'is_active', 'sort'];

    protected static function newFactory(): PlanFactory
    {
        return PlanFactory::new();
    }

    protected function casts(): array
    {
        return [
            'tier' => PlanTier::class,
            'price_idr' => 'integer',
            'duration_days' => 'integer',
            'features' => 'array',
            'limits' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isFree(): bool
    {
        return $this->tier === PlanTier::Free;
    }

    /** The free plan: the `plans` row if seeded, otherwise an unsaved instance from config/entitlements.php. */
    public static function free(): self
    {
        return static::query()->where('key', 'free')->first() ?? static::fromConfig((array) config('entitlements.free'));
    }

    /** @param array<string,mixed> $config */
    public static function fromConfig(array $config): self
    {
        $plan = new self;
        $plan->forceFill([
            'key' => $config['key'], 'name' => $config['name'], 'tier' => $config['tier'],
            'duration_days' => $config['duration_days'] ?? null, 'features' => $config['features'] ?? [],
            'limits' => $config['limits'] ?? [], 'is_active' => true,
        ]);

        return $plan;
    }
}
