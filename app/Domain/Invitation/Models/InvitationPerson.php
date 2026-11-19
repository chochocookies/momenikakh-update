<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Models;

use App\Domain\Invitation\Enums\PersonRole;
use App\Domain\Media\Concerns\RegistersPersonMedia;
use Database\Factories\InvitationPersonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $invitation_id
 * @property PersonRole $role
 * @property string $name
 * @property string|null $nickname
 * @property string|null $parents_text
 * @property string|null $bio
 * @property Carbon|null $birth_date
 * @property string|null $instagram
 * @property int $sort
 */
class InvitationPerson extends Model implements HasMedia
{
    /** @use HasFactory<InvitationPersonFactory> */
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

    /** @return BelongsTo<Invitation, $this> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
