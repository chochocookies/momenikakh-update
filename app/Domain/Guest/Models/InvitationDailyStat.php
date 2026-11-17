<?php

declare(strict_types=1);

namespace App\Domain\Guest\Models;

use App\Domain\Invitation\Models\Invitation;
use Database\Factories\InvitationDailyStatFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationDailyStat extends Model
{
    use HasFactory;

    protected $table = 'invitation_daily_stats';

    protected $fillable = ['date', 'views', 'unique_visitors', 'opens'];

    protected static function newFactory(): InvitationDailyStatFactory
    {
        return InvitationDailyStatFactory::new();
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'views' => 'integer',
            'unique_visitors' => 'integer',
            'opens' => 'integer',
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
