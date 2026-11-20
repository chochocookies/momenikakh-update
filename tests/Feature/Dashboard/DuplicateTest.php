<?php

declare(strict_types=1);

use App\Domain\Guest\Models\Guest;
use App\Domain\Invitation\Data\InvitationData;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

require_once __DIR__.'/../Media/MediaTestCase.php';

beforeEach(function () {
    $this->withoutVite();
    useFakeMediaDisk();
    $this->owner = User::factory()->create();
    $this->stranger = User::factory()->create();
    $this->source = Invitation::factory()->for($this->owner)->wedding()->complete()->published()->create(['title' => 'Raka & Sinta']);
    $this->source->events()->first()->update(['timezone' => 'Asia/Makassar']);
    $this->copy = function () {
        $this->actingAs($this->owner)->post(route('dashboard.invitations.duplicate', $this->source))->assertRedirect();

        return Invitation::query()->where('id', '!=', $this->source->id)->where('user_id', $this->owner->id)->latest('id')->firstOrFail();
    };
});

/** What the owner wrote, without ids, slug, title and status (those differ on purpose). */
function writtenData(Invitation $i): array
{
    $d = InvitationData::fromModel($i->fresh())->toArray();
    foreach (['people', 'story', 'gifts'] as $key) {
        $d[$key] = array_map(fn ($row) => array_diff_key($row, ['id' => 1]), $d[$key]);
    }
    $d['events'] = array_map(fn ($row) => array_diff_key($row, ['id' => 1]), $d['events']);
    unset($d['id'], $d['slug'], $d['title'], $d['status']);

    return $d;
}

it('makes a draft copy with a new link, a new title and the same data', function () {
    $copy = ($this->copy)();

    expect($copy->status)->toBe(InvitationStatus::Draft)->and($copy->published_at)->toBeNull()
        ->and($copy->slug)->not->toBe($this->source->slug)->and($copy->title)->toBe('Salinan Raka & Sinta')
        ->and($copy->template_slug)->toBe($this->source->template_slug)
        ->and(writtenData($copy))->toEqual(writtenData($this->source));
});

it('keeps the zone of an event that had its own, and leaves the others following the invitation', function () {
    $copy = ($this->copy)();

    expect($copy->events()->orderBy('sort')->pluck('timezone')->all())->toBe($this->source->events()->orderBy('sort')->pluck('timezone')->all());
});

it('does not carry over guests, answers, wishes or the plan, and leaves the original alone', function () {
    Guest::factory()->for($this->source)->create();
    $before = writtenData($this->source);

    $copy = ($this->copy)();

    expect($copy->guests()->count())->toBe(0)->and($copy->rsvps()->count())->toBe(0)->and($copy->messages()->count())->toBe(0)
        ->and($this->source->fresh()->status)->toBe(InvitationStatus::Published)->and($this->source->guests()->count())->toBe(1)
        ->and(writtenData($this->source))->toEqual($before);
});

it('copies the files, so the copy has its own and deleting the original does not break it', function () {
    $this->source->addMedia(fakeUpload('ok-800x600.jpg'))->toMediaCollection('cover');
    $this->source->addMedia(fakeUpload('ok-800x600.jpg'))->toMediaCollection('gallery');
    $this->source->people()->orderBy('sort')->first()->addMedia(fakeUpload('ok-800x600.jpg'))->toMediaCollection('photo');

    $copy = ($this->copy)();

    expect($copy->getMedia('cover'))->toHaveCount(1)->and($copy->getMedia('gallery'))->toHaveCount(1)
        ->and($copy->people()->orderBy('sort')->first()->getMedia('photo'))->toHaveCount(1);
    $copied = $copy->getFirstMedia('cover');
    expect($copied->id)->not->toBe($this->source->getFirstMedia('cover')->id)->and($copied->uuid)->not->toBe($this->source->getFirstMedia('cover')->uuid);

    $path = $copied->getPathRelativeToRoot();
    $this->source->forceDelete();
    Storage::disk('media')->assertExists($path);
});

it('keeps other people out and visitors at the login page', function () {
    $this->post(route('dashboard.invitations.duplicate', $this->source))->assertRedirect(route('login'));
    $this->actingAs($this->stranger)->post(route('dashboard.invitations.duplicate', $this->source))->assertForbidden();

    expect(Invitation::query()->count())->toBe(1);
});

it('offers the button on the invitation page', function () {
    $this->actingAs($this->owner)->get(route('dashboard.invitations.show', $this->source))->assertSee(route('dashboard.invitations.duplicate', $this->source), false);
});
