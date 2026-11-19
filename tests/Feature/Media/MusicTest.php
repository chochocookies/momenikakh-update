<?php

declare(strict_types=1);

use App\Domain\Media\MediaCollection;
use App\Domain\Media\Models\MusicTrack;
use App\Domain\Media\Services\MediaUploader;
use App\Domain\Media\Services\MusicResolver;
use App\Models\User;
use Illuminate\Http\UploadedFile;

require_once __DIR__.'/MediaTestCase.php';

beforeEach(function () {
    useFakeMediaDisk();
    [$this->owner, $this->invitation] = newOwnerWithInvitation();
    $this->mp3 = function (int $bytes = 4000, string $name = 'lagu.mp3'): UploadedFile {
        return UploadedFile::fake()->createWithContent($name, "\xFF\xFB\x90\x64".str_repeat("\0", $bytes));
    };
    $this->upload = fn (UploadedFile $f, ?User $as = null) => $this->actingAs($as ?? $this->owner)
        ->postJson(route('dashboard.media.store', [$this->invitation, 'music']), ['file' => $f]);
});

it('needs the custom_music ability to upload music', function () {
    ($this->upload)(($this->mp3)())->assertForbidden();
    grantPlan($this->invitation, features: ['custom_music' => true]);
    ($this->upload)(($this->mp3)())->assertCreated();
    $media = $this->invitation->media()->where('collection_name', 'music')->firstOrFail();
    expect($media->file_name)->toMatch('/^[0-9a-f-]{36}\.mp3$/');
});

it('rejects non mp3/ogg, oversize and mismatching files', function () {
    grantPlan($this->invitation, features: ['custom_music' => true]);
    ($this->upload)(UploadedFile::fake()->createWithContent('a.wav', "RIFF\x24\0\0\0WAVEfmt ".str_repeat("\0", 40)))->assertUnprocessable();
    ($this->upload)(UploadedFile::fake()->createWithContent('evil.mp3', "MZ\x90\x00".str_repeat("\0", 300)))->assertUnprocessable();
    ($this->upload)(($this->mp3)(6 * 1024 * 1024 + 10))->assertUnprocessable();                 // over the default 6 MB
    ($this->upload)(($this->mp3)(4000, 'lagu.ogg'))->assertUnprocessable();                     // extension does not match the content
    expect($this->invitation->media()->count())->toBe(0);
});

it('keeps a single music file per invitation', function () {
    grantPlan($this->invitation, features: ['custom_music' => true]);
    ($this->upload)(($this->mp3)())->assertCreated();
    ($this->upload)(($this->mp3)())->assertCreated();
    expect($this->invitation->media()->where('collection_name', 'music')->count())->toBe(1);
});

it('resolves uploaded music for the public page', function () {
    grantPlan($this->invitation, features: ['custom_music' => true]);
    ($this->upload)(($this->mp3)())->assertCreated();
    $this->invitation->update(['settings' => ['music' => ['source' => 'upload', 'title' => 'Lagu kami']]]);

    $music = app(MusicResolver::class)->for($this->invitation->fresh());
    expect($music->title)->toBe('Lagu kami')->and($music->url)->toContain('.mp3');
});

it('renders no music when nothing is configured', function () {
    expect(app(MusicResolver::class)->for($this->invitation))->toBeNull();
    $this->invitation->update(['settings' => ['music' => ['source' => 'upload']]]);
    expect(app(MusicResolver::class)->for($this->invitation->fresh()))->toBeNull();   // chosen but no file uploaded
});

describe('licensed catalog (DEC-06)', function () {
    beforeEach(function () {
        $this->track = MusicTrack::factory()->create(['title' => 'Lagu Berlisensi', 'requires_attribution' => true, 'attribution_text' => 'Musik oleh Artis Contoh']);
        // Keep the fake upload alive: its temp file is deleted as soon as the object is garbage collected.
        $this->audioFile = UploadedFile::fake()->createWithContent('t.mp3', "\xFF\xFB\x90\x64".str_repeat("\0", 2000));
        app(MediaUploader::class)->attachAudio($this->track, MediaCollection::Audio, $this->audioFile->getRealPath());
        $this->invitation->update(['settings' => ['music' => ['source' => 'catalog', 'track_id' => $this->track->id]]]);
    });

    it('is off by default', function () {
        expect(config('media-limits.audio.catalog_enabled'))->toBeFalse();
        expect(app(MusicResolver::class)->for($this->invitation->fresh()))->toBeNull();
    });

    it('serves a licensed, verified track once enabled, with its attribution', function () {
        config(['media-limits.audio.catalog_enabled' => true]);
        $music = app(MusicResolver::class)->for($this->invitation->fresh());
        expect($music->title)->toBe('Lagu Berlisensi')->and($music->attribution)->toBe('Musik oleh Artis Contoh');
    });

    it('never serves a track without a licence, verification, or while inactive', function (string $state) {
        config(['media-limits.audio.catalog_enabled' => true]);
        $this->track->forceFill(match ($state) {
            'unverified' => ['verified_at' => null],
            'unlicensed' => ['license' => null],
            'blank licence' => ['license' => ''],
            'inactive' => ['is_active' => false],
        })->save();
        expect(app(MusicResolver::class)->for($this->invitation->fresh()))->toBeNull();
        expect(MusicTrack::query()->selectable()->count())->toBe(0);
    })->with(['unverified', 'unlicensed', 'blank licence', 'inactive']);
});
