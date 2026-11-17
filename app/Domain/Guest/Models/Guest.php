<?php

declare(strict_types=1);

namespace App\Domain\Guest\Models;

use App\Domain\Invitation\Models\Invitation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Database\Factories\GuestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guest extends Model
{
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

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }
}
