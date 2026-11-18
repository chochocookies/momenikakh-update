<?php

declare(strict_types=1);

namespace App\Domain\Guest\Models;

use App\Domain\Guest\Enums\Attendance;
use App\Domain\Invitation\Models\Invitation;
use Database\Factories\RsvpFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rsvp extends Model
{
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

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
