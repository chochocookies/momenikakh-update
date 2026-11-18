<?php

declare(strict_types=1);

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Models\InvitationGift;
use App\Domain\Invitation\Models\InvitationPerson;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

require_once __DIR__.'/MediaTestCase.php';

beforeEach(function () {
    useFakeMediaDisk();
    [$this->owner, $this->invitation] = newOwnerWithInvitation();
    $this->upload = fn (string $collection, UploadedFile $file, ?User $as = null) => $this->actingAs($as ?? $this->owner)
        ->postJson(route('dashboard.media.store', [$this->invitation, $collection]), ['file' => $file]);
});

it('stores a clean, uuid-named copy of a valid cover photo', function () {
    $res = ($this->upload)('cover', fakeUpload('big.jpg', 'Foto Pre-Wedding <script>.jpg'))->assertCreated();

    $media = $this->invitation->media()->where('collection_name', 'cover')->firstOrFail();
    expect($media->file_name)->toMatch('/^[0-9a-f-]{36}\.webp$/');          // client name is never stored
    expect($media->name)->toBe('cover');
    expect($media->mime_type)->toBe('image/webp');
    expect((int) $media->getCustomProperty('width'))->toBe(2400);           // capped at the sanitized max edge
    expect($res->json('url'))->toContain($media->uuid);
    expect(Storage::disk('media')->exists($media->getPathRelativeToRoot()))->toBeTrue();
});

it('strips EXIF and GPS from the stored original', function () {
    ($this->upload)('gallery', fakeUpload('orient-6.jpg'))->assertCreated();
    $media = $this->invitation->media()->firstOrFail();
    $bytes = Storage::disk('media')->get($media->getPathRelativeToRoot());
    expect($bytes)->not->toContain("Exif\0\0")->not->toContain('SECRET-LOCATION-MARKER')->not->toContain("\xFF\xE1");
});

it('stores a polyglot image without its trailing code', function () {
    ($this->upload)('gallery', fakeUpload('polyglot.jpg'))->assertCreated();
    $bytes = Storage::disk('media')->get($this->invitation->media()->firstOrFail()->getPathRelativeToRoot());
    expect($bytes)->not->toContain('<?php');
});

it('rejects fake, hostile and unusable images with a clear 422', function (string $fixture, string $clientName, string $expected) {
    ($this->upload)('gallery', fakeUpload($fixture, $clientName))
        ->assertUnprocessable()->assertJsonPath('errors.file.0', $expected);
    expect($this->invitation->media()->count())->toBe(0);
    expect(Storage::disk('media')->allFiles())->toBeEmpty();
})->with([
    'php renamed jpg' => ['fake-php.jpg', 'x.jpg', 'Format berkas tidak didukung. Gunakan JPEG, PNG, atau WebP.'],
    'svg renamed png' => ['svg-as.png', 'x.png', 'Format SVG tidak didukung. Gunakan JPEG, PNG, atau WebP.'],
]);

it('rejects images that are too small, too big in pixels, or corrupt', function () {
    ($this->upload)('cover', fakeUpload('tiny.jpg'))->assertUnprocessable();                      // below the cover minimum
    ($this->upload)('gallery', fakeUpload('bomb.png', 'bom.png'))->assertUnprocessable();          // 30000x30000 header
    expect($this->invitation->media()->count())->toBe(0);
});

it('requires login and ownership', function () {
    $this->postJson(route('dashboard.media.store', [$this->invitation, 'cover']), ['file' => fakeUpload('big.jpg')])->assertUnauthorized();
    ($this->upload)('cover', fakeUpload('big.jpg'), User::factory()->create())->assertForbidden();
    expect($this->invitation->media()->count())->toBe(0);
});

it('only accepts known collections', function () {
    $this->actingAs($this->owner)->postJson('/dashboard/invitations/'.$this->invitation->id.'/media/evil', ['file' => fakeUpload('big.jpg')])->assertNotFound();
});

it('replaces the previous cover (single file collection)', function () {
    ($this->upload)('cover', fakeUpload('big.jpg'))->assertCreated();
    $first = $this->invitation->media()->firstOrFail();
    ($this->upload)('cover', fakeUpload('big.jpg'))->assertCreated();
    expect($this->invitation->media()->where('collection_name', 'cover')->count())->toBe(1);
    expect(Storage::disk('media')->exists($first->getPathRelativeToRoot()))->toBeFalse();   // old files removed
});

it('limits gallery photos to the plan allowance', function () {
    grantPlan($this->invitation, limits: ['max_photos' => 2]);
    ($this->upload)('gallery', fakeUpload('ok-800x600.jpg'))->assertCreated();
    ($this->upload)('gallery', fakeUpload('ok-800x600.jpg'))->assertCreated();
    ($this->upload)('gallery', fakeUpload('ok-800x600.jpg'))->assertUnprocessable();
    expect($this->invitation->media()->where('collection_name', 'gallery')->count())->toBe(2);
});

it('attaches a person photo and a gift QR to the right owner only', function () {
    $person = InvitationPerson::factory()->for($this->invitation)->create();
    $gift = InvitationGift::factory()->for($this->invitation)->create();

    $this->actingAs($this->owner)->postJson(route('dashboard.media.person-photo', [$this->invitation, $person]), ['file' => fakeUpload('ok-800x600.jpg')])->assertCreated();
    $this->actingAs($this->owner)->postJson(route('dashboard.media.gift-qr', [$this->invitation, $gift]), ['file' => fakeUpload('transparent.png', 'qr.png')])->assertCreated();

    expect($person->media()->count())->toBe(1)->and($gift->media()->count())->toBe(1);
    $qr = $gift->media()->first();
    expect($qr->mime_type)->toBe('image/png')->and($qr->file_name)->toEndWith('.png');

    $otherPerson = InvitationPerson::factory()->for(Invitation::factory()->create())->create();
    $this->actingAs($this->owner)->postJson(route('dashboard.media.person-photo', [$this->invitation, $otherPerson]), ['file' => fakeUpload('ok-800x600.jpg')])->assertNotFound();
});

it('deletes only media that belongs to the invitation', function () {
    ($this->upload)('gallery', fakeUpload('ok-800x600.jpg'));
    $media = $this->invitation->media()->firstOrFail();
    $path = $media->getPathRelativeToRoot();

    [$otherOwner, $otherInvitation] = newOwnerWithInvitation();
    $this->actingAs($otherOwner)->deleteJson(route('dashboard.media.destroy', [$otherInvitation, $media]))->assertNotFound();
    expect(Storage::disk('media')->exists($path))->toBeTrue();

    $this->actingAs($this->owner)->deleteJson(route('dashboard.media.destroy', [$this->invitation, $media]))->assertNoContent();
    expect(Storage::disk('media')->exists($path))->toBeFalse();
});

it('reorders the gallery and refuses ids from elsewhere', function () {
    foreach (range(1, 3) as $_) {
        ($this->upload)('gallery', fakeUpload('ok-800x600.jpg'));
    }
    $ids = $this->invitation->media()->orderBy('id')->pluck('id')->all();
    $this->actingAs($this->owner)->putJson(route('dashboard.media.reorder', $this->invitation), ['ids' => array_reverse($ids)])->assertOk();
    expect($this->invitation->media()->orderBy('order_column')->pluck('id')->all())->toBe(array_reverse($ids));

    $foreign = Invitation::factory()->create();
    ($this->upload)('gallery', fakeUpload('ok-800x600.jpg'));
    $this->actingAs($this->owner)->putJson(route('dashboard.media.reorder', $this->invitation), ['ids' => [999999]])->assertUnprocessable();
});
