<?php

declare(strict_types=1);

namespace App\Domain\Guest\Models;

use App\Domain\Invitation\Models\Invitation;
use Illuminate\Database\Eloquent\Builder;
use Database\Factories\GuestMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestMessage extends Model
{
    use HasFactory;

    protected $table = 'guest_messages';

    protected $fillable = ['name', 'message', 'is_visible'];

    protected static function newFactory(): GuestMessageFactory
    {
        return GuestMessageFactory::new();
    }

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }
}
