<?php

declare(strict_types=1);

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Media\MediaCollection;
use App\Domain\Media\Models\MusicTrack;
use App\Domain\Media\Services\MediaUploader;
use App\Livewire\Editor\InvitationEditor;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\Support\Surfaces;

require_once dirname(__DIR__).'/Media/MediaTestCase.php';

beforeEach(function () {
    $this->withoutVite();
    URL::forceRootUrl(Surfaces::url('app', ''));
    useFakeMediaDisk();
    $this->owner = User::factory()->create();
    $this->invitation = Invitation::factory()->for($this->owner)->wedding()->complete()->create();
    $this->editor = fn (?Invitation $i = null) => Livewire::actingAs($this->owner)->test(InvitationEditor::class, ['invitation' => $i ?? $this->invitation]);
});

it('shows gallery and music steps in the editor', function () {
    ($this->editor)()
        ->assertSee('Galeri')
        ->assertSee('Musik')
        ->call('selectStep', 'gallery')
        ->assertSee('Lepaskan foto di sini')
        ->call('selectStep', 'music')
        ->assertSee('Sumber musik');
});

it('attaches gallery photos through the editor and lists them', function () {
    ($this->editor)()
        ->call('selectStep', 'gallery')
        ->set('galleryPhotos', [fakeUpload('ok-800x600.jpg')])
        ->assertHasNoErrors('galleryPhotos');

    expect($this->invitation->fresh()->getMedia('gallery'))->toHaveCount(1)
        ->and(($this->editor)()->get('galleryItems'))->toHaveCount(1);
});

it('rejects an oversized gallery photo with a clear message', function () {
    $tooBig = UploadedFile::fake()->create('besar.jpg', intdiv((int) config('media-limits.image.max_bytes'), 1024) + 100);

    ($this->editor)()
        ->call('selectStep', 'gallery')
        ->set('galleryPhotos', [$tooBig])
        ->assertHasErrors(['galleryPhotos.0'])
        ->assertSee('Berkas terlalu besar');

    expect($this->invitation->fresh()->getMedia('gallery'))->toHaveCount(0);
});

it('reorders and removes gallery photos', function () {
    $uploader = app(MediaUploader::class);
    foreach (range(1, 3) as $_) {
        $uploader->attachImage($this->invitation, MediaCollection::Gallery, fakeUpload('ok-800x600.jpg'));
    }
    $ids = $this->invitation->fresh()->getMedia('gallery')->pluck('id')->all();

    ($this->editor)()
        ->call('selectStep', 'gallery')
        ->call('reorderGallery', array_reverse($ids))
        ->assertHasNoErrors();

    expect($this->invitation->fresh()->getMedia('gallery')->pluck('id')->all())->toBe(array_reverse($ids));

    ($this->editor)()->call('removeGalleryItem', $ids[0]);
    expect($this->invitation->fresh()->getMedia('gallery')->pluck('id')->all())->not->toContain($ids[0]);
});

it('refuses music upload without the custom_music ability', function () {
    $mp3 = UploadedFile::fake()->createWithContent('lagu.mp3', "\xFF\xFB\x90\x64".str_repeat("\0", 4000));

    ($this->editor)()
        ->call('selectStep', 'music')
        ->set('musicFile', $mp3)
        ->assertHasErrors(['musicFile'])
        ->assertSee('paket tertentu');

    expect($this->invitation->fresh()->getMedia('music'))->toHaveCount(0);
});

it('uploads music when entitled and stores the music settings', function () {
    grantPlan($this->invitation, features: ['custom_music' => true]);
    $mp3 = UploadedFile::fake()->createWithContent('lagu.mp3', "\xFF\xFB\x90\x64".str_repeat("\0", 4000));

    ($this->editor)()
        ->call('selectStep', 'music')
        ->set('musicForm.source', 'upload')
        ->set('musicForm.title', 'Lagu kami')
        ->set('musicFile', $mp3)
        ->assertHasNoErrors();

    $invitation = $this->invitation->fresh();
    expect($invitation->getMedia('music'))->toHaveCount(1)
        ->and($invitation->settings['music']['source'])->toBe('upload')
        ->and($invitation->settings['music']['title'])->toBe('Lagu kami');
});

it('rejects an oversized music file with a clear message', function () {
    grantPlan($this->invitation, features: ['custom_music' => true]);
    $tooBig = UploadedFile::fake()->create('lagu.mp3', intdiv((int) config('media-limits.audio.max_bytes'), 1024) + 50);

    ($this->editor)()
        ->call('selectStep', 'music')
        ->set('musicFile', $tooBig)
        ->assertHasErrors(['musicFile'])
        ->assertSee('Berkas terlalu besar');
});

it('picks a catalog track when the catalog is enabled', function () {
    config(['media-limits.audio.catalog_enabled' => true]);
    $track = MusicTrack::factory()->create(['title' => 'Lagu Katalog', 'requires_attribution' => false]);

    ($this->editor)()
        ->call('selectStep', 'music')
        ->set('musicForm.source', 'catalog')
        ->assertHasErrors(['musicForm.track_id'])
        ->set('musicForm.track_id', $track->id)
        ->assertHasNoErrors();

    expect($this->invitation->fresh()->settings['music'])->toMatchArray([
        'source' => 'catalog',
        'track_id' => $track->id,
    ]);
});

it('checks slug uniqueness live while editing basics', function () {
    Invitation::factory()->for(User::factory())->create(['slug' => 'sudah-dipakai']);

    ($this->editor)()
        ->set('form.slug', 'sudah-dipakai')
        ->assertHasErrors(['form.slug'])
        ->assertSee('tidak tersedia')
        ->set('form.slug', 'admin')
        ->assertHasErrors(['form.slug'])
        ->set('form.slug', 'raka-sinta-baru')
        ->assertHasNoErrors();

    expect($this->invitation->fresh()->slug)->toBe('raka-sinta-baru');
});
