<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Models;

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Guest\Models\Guest;
use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Guest\Models\InvitationDailyStat;
use App\Domain\Guest\Models\Rsvp;
use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Media\Concerns\RegistersInvitationMedia;
use App\Models\User;
use Database\Factories\InvitationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Invitation extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use RegistersInvitationMedia;
    use SoftDeletes;

    protected $table = 'invitations';

    /** user_id is never mass-assignable: create through $user->invitations()->create(). */
    protected $fillable = [
        'event_type', 'template_slug', 'slug', 'title', 'status', 'locale', 'timezone',
        'content', 'settings', 'published_at', 'expires_at',
    ];

    protected $attributes = [
        'status' => 'draft',
        'locale' => 'id',
        'timezone' => 'Asia/Jakarta',
        'content' => '[]',
        'settings' => '[]',
    ];

    protected static function newFactory(): InvitationFactory
    {
        return InvitationFactory::new();
    }

    protected function casts(): array
    {
        return [
            'event_type' => EventType::class,
            'status' => InvitationStatus::class,
            'content' => 'array',
            'settings' => 'array',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->registerInvitationMediaCollections();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerInvitationMediaConversions($media);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Plan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /** @return HasMany<InvitationPerson, $this> */
    public function people(): HasMany
    {
        return $this->hasMany(InvitationPerson::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(InvitationEvent::class)->orderBy('sort')->orderBy('starts_at');
    }

    public function storyItems(): HasMany
    {
        return $this->hasMany(InvitationStoryItem::class)->orderBy('sort')->orderBy('id');
    }

    public function gifts(): HasMany
    {
        return $this->hasMany(InvitationGift::class)->orderBy('sort')->orderBy('id');
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(GuestMessage::class);
    }

    public function dailyStats(): HasMany
    {
        return $this->hasMany(InvitationDailyStat::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /** Only invitations owned by the given user (DOM-08). */
    public function scopeOwnedBy(Builder $query, User|int $user): Builder
    {
        return $query->where($this->qualifyColumn('user_id'), $user instanceof User ? $user->getKey() : $user);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('status'), InvitationStatus::Published->value);
    }

    public function isPublished(): bool
    {
        return $this->status === InvitationStatus::Published;
    }

    public function isOwnedBy(User $user): bool
    {
        return (int) $this->user_id === (int) $user->getKey();
    }
}
