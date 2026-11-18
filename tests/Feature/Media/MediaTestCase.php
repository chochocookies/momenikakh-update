<?php

declare(strict_types=1);

// Shared helpers for the media tests. Required once from tests/Pest.php or by each file below.

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function mediaFixture(string $name): string
{
    return base_path("tests/fixtures/{$name}");
}

function fakeUpload(string $fixture, string $clientName = 'foto.jpg'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($clientName, (string) file_get_contents(mediaFixture($fixture)));
}

/** Fakes the media disk and points the app at it. Call from beforeEach(). */
function useFakeMediaDisk(): void
{
    Storage::fake('media');
    config(['media-limits.disk' => 'media', 'media-library.disk_name' => 'media']);
}

/** Gives the invitation's owner a plan with the given features and limits. */
function grantPlan(Invitation $invitation, array $features = [], array $limits = []): Plan
{
    $plan = Plan::factory()->withFeatures($features)->withLimits($limits)->create();
    Subscription::factory()->for($invitation->user)->create(['plan_id' => $plan->id, 'invitation_id' => $invitation->id]);

    return $plan;
}

function newOwnerWithInvitation(): array
{
    $owner = User::factory()->create();

    return [$owner, Invitation::factory()->for($owner)->create()];
}
