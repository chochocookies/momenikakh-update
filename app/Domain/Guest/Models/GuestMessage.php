<?php

declare(strict_types=1);

namespace App\Domain\Guest\Models;

use App\Domain\Invitation\Models\Invitation;
use Database\Factories\GuestMessageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invitation_id
 * @property int|null $guest_id
 * @property string $name
 * @property string $message
 * @property bool $is_visible
 */
class GuestMessage extends Model
{
    /** @use HasFactory<GuestMessageFactory> */
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

    /** @return BelongsTo<Invitation, $this> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    /** @return BelongsTo<Guest, $this> */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }
}
