<?php

declare(strict_types=1);

namespace App\Domain\Guest\Models;

use App\Domain\Guest\Enums\Attendance;
use App\Domain\Invitation\Models\Invitation;
use Database\Factories\RsvpFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $invitation_id
 * @property int|null $guest_id
 * @property string $name
 * @property Attendance $attendance
 * @property int $pax
 * @property string|null $note
 * @property string|null $ip_hash
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Rsvp extends Model
{
    /** @use HasFactory<RsvpFactory> */
    use HasFactory;

    protected $table = 'rsvps';

    protected $fillable = ['name', 'attendance', 'pax', 'note', 'ip_hash'];

    protected static function newFactory(): RsvpFactory
    {
        return RsvpFactory::new();
    }

    protected function casts(): array
    {
        return [
            'attendance' => Attendance::class,
            'pax' => 'integer',
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
}
