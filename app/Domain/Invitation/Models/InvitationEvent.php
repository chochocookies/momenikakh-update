<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Models;

use App\Domain\Invitation\Enums\EventKind;
use Database\Factories\InvitationEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $invitation_id
 * @property EventKind $kind
 * @property string|null $label
 * @property Carbon $starts_at
 * @property Carbon|null $ends_at
 * @property string $venue_name
 * @property string $address
 * @property string|null $maps_url
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $dress_code
 * @property string|null $notes
 * @property int $sort
 */
class InvitationEvent extends Model
{
    /** @use HasFactory<InvitationEventFactory> */
    use HasFactory;

    protected $table = 'invitation_events';

    protected $fillable = ['kind', 'label', 'starts_at', 'ends_at', 'venue_name', 'address', 'maps_url', 'latitude', 'longitude', 'dress_code', 'notes', 'sort'];

    protected static function newFactory(): InvitationEventFactory
    {
        return InvitationEventFactory::new();
    }

    protected function casts(): array
    {
        return [
            'kind' => EventKind::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'sort' => 'integer',
        ];
    }

    /** @return BelongsTo<Invitation, $this> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
