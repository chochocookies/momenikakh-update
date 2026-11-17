<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Services\InvitationWriter;
use App\Domain\Invitation\Support\SamplePayload;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Invitation> */
class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        $title = fake('id_ID')->firstName().' & '.fake('id_ID')->firstName();

        return [
            'user_id' => User::factory(),
            'event_type' => EventType::Wedding,
            'template_slug' => 'classic-elegant',
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'title' => $title,
            'status' => InvitationStatus::Draft,
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
            'content' => [],
            'settings' => [],
        ];
    }

    public function forType(EventType $type): static
    {
        return $this->state(fn () => ['event_type' => $type]);
    }

    public function wedding(): static { return $this->forType(EventType::Wedding); }
    public function walimatulUrs(): static { return $this->forType(EventType::WalimatulUrs); }
    public function engagement(): static { return $this->forType(EventType::Engagement); }
    public function aqiqah(): static { return $this->forType(EventType::Aqiqah); }
    public function khitanan(): static { return $this->forType(EventType::Khitanan); }
    public function birthday(): static { return $this->forType(EventType::Birthday); }

    /** Fills people, events, story, gifts, content and settings with valid fictional data for the event type. */
    public function complete(): static
    {
        return $this->afterCreating(function (Invitation $invitation): void {
            app(InvitationWriter::class)->applyAll($invitation, SamplePayload::for($invitation->event_type));
        });
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => InvitationStatus::Draft]);
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => InvitationStatus::Published, 'published_at' => now(), 'expires_at' => null]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => InvitationStatus::Expired, 'published_at' => now()->subDays(40), 'expires_at' => now()->subDay(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => InvitationStatus::Archived]);
    }
}
