<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Models;

use App\Domain\Invitation\Enums\GiftType;
use App\Domain\Media\Concerns\RegistersGiftMedia;
use Database\Factories\InvitationGiftFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class InvitationGift extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use RegistersGiftMedia;

    protected $table = 'invitation_gifts';

    protected $fillable = ['type', 'provider', 'holder_name', 'number', 'address_text', 'is_active', 'sort'];

    protected static function newFactory(): InvitationGiftFactory
    {
        return InvitationGiftFactory::new();
    }

    protected function casts(): array
    {
        return [
            'type' => GiftType::class,
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->registerGiftMediaCollections();
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
