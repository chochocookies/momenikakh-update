<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Models;

use App\Domain\Invitation\Enums\PersonRole;
use App\Domain\Media\Concerns\RegistersPersonMedia;
use Database\Factories\InvitationPersonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class InvitationPerson extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use RegistersPersonMedia;

    protected $table = 'invitation_people';

    protected $fillable = ['role', 'name', 'nickname', 'parents_text', 'bio', 'birth_date', 'instagram', 'sort'];

    protected static function newFactory(): InvitationPersonFactory
    {
        return InvitationPersonFactory::new();
    }

    protected function casts(): array
    {
        return [
            'role' => PersonRole::class,
            'birth_date' => 'date',
            'sort' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->registerPersonMediaCollections();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerPersonMediaConversions($media);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
