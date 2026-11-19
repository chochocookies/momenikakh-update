<?php

declare(strict_types=1);

namespace App\Domain\Guest\Models;

use App\Domain\Invitation\Models\Invitation;
use Database\Factories\GuestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $invitation_id
 * @property string $name
 * @property string|null $group
 * @property string|null $phone
 * @property string $token
 * @property Carbon|null $opened_at
 */
class Guest extends Model
{
    /** @use HasFactory<GuestFactory> */
    use HasFactory;

    protected $table = 'guests';

    protected $fillable = ['name', 'group', 'phone'];

    protected static function newFactory(): GuestFactory
    {
        return GuestFactory::new();
    }

    protected static function booted(): void
    {
        static::creating(function (self $guest): void {
            if (blank($guest->token)) {
                $guest->token = Str::lower(Str::random(12));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Invitation, $this> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    /** @return HasMany<Rsvp, $this> */
    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }
}
