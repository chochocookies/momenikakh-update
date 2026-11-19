<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Services;

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Support\SamplePayload;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Creates a draft invitation for a user (DSH-02). Every new invitation is a free sample until the Paket Template is
 * bought (DEC-01): it can be filled in and previewed, not published. "Start with sample data" fills it with the
 * fictional data of the event type so every section already has something to show.
 */
final class InvitationCreator
{
    public function __construct(private readonly InvitationWriter $writer, private readonly SlugGenerator $slugs) {}

    public function create(User $user, EventType $type, string $templateSlug, string $title, bool $withSampleData): Invitation
    {
        return DB::transaction(function () use ($user, $type, $templateSlug, $title, $withSampleData): Invitation {
            // user_id is never mass assignable: the invitation is created through the owner's relation.
            $invitation = $user->invitations()->create([
                'event_type' => $type,
                'template_slug' => $templateSlug,
                'slug' => $this->slugs->generate($title),
                'title' => $title,
                'status' => InvitationStatus::Draft,
            ]);

            if ($withSampleData) {
                $payload = SamplePayload::for($type);
                // The owner's title and template win over the sample's.
                $payload['basics']['title'] = $title;
                $payload['basics']['template_slug'] = $templateSlug;
                $this->writer->applyAll($invitation, $payload);
            }

            return $invitation->refresh();
        });
    }
}
