<?php

declare(strict_types=1);

use App\Domain\Invitation\Models\InvitationGift;
use App\Domain\Invitation\Models\InvitationPerson;
use App\Domain\Media\Data\MediaData;
use App\Domain\Media\MediaCollection;
use App\Domain\Media\Services\MediaUploader;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob;

require_once __DIR__.'/MediaTestCase.php';

// QUEUE_CONNECTION=sync (phpunit.xml) makes conversions run inside the upload call.

beforeEach(function () {
    useFakeMediaDisk();
    [$this->owner, $this->invitation] = newOwnerWithInvitation();
    $this->uploader = app(MediaUploader::class);
    $this->dims = function ($media, string $conversion): array {
        $path = Storage::disk('media')->path($media->getPathRelativeToRoot($conversion));
        expect(is_file($path))->toBeTrue("conversion {$conversion} was not generated");
        $info = getimagesize($path);

        return [$info[0], $info[1], $info['mime']];
    };
});

it('creates 480, 960 and 1600 wide WebP versions plus a thumbnail and an OG image for a cover', function () {
    $media = $this->uploader->attachImage($this->invitation, MediaCollection::Cover, mediaFixture('big.jpg'));

    expect(($this->dims)($media, 'sm'))->toBe([480, 320, 'image/webp']);
    expect(($this->dims)($media, 'md'))->toBe([960, 640, 'image/webp']);
    [$lgW, $lgH, $lgMime] = ($this->dims)($media, 'lg');                            // 2400x1600 original, ratio kept
    expect([$lgW, $lgMime])->toBe([1600, 'image/webp'])->and(abs($lgH - 1067))->toBeLessThanOrEqual(1);
    expect(($this->dims)($media, 'thumb'))->toBe([320, 320, 'image/webp']);
    expect(($this->dims)($media, 'og'))->toBe([1200, 630, 'image/jpeg']);            // JPEG on purpose: link-preview scrapers
    foreach (['sm', 'md', 'lg', 'thumb', 'og'] as $c) {
        expect($media->fresh()->hasGeneratedConversion($c))->toBeTrue($c);
    }
});

it('never upscales a small original', function () {
    $media = $this->uploader->attachImage($this->invitation, MediaCollection::Gallery, mediaFixture('ok-800x600.jpg'));
    [$mdW] = ($this->dims)($media, 'md');
    [$lgW] = ($this->dims)($media, 'lg');
    expect($mdW)->toBeLessThanOrEqual(800)->and($lgW)->toBeLessThanOrEqual(800);
    expect(($this->dims)($media, 'sm')[0])->toBe(480);
});

it('keeps every generated file free of EXIF and GPS data', function () {
    $media = $this->uploader->attachImage($this->invitation, MediaCollection::Gallery, mediaFixture('orient-6.jpg'));
    foreach (['sm', 'md', 'lg', 'thumb'] as $c) {
        $bytes = Storage::disk('media')->get($media->getPathRelativeToRoot($c));
        expect($bytes)->not->toContain("Exif\0\0")->not->toContain('SECRET-LOCATION-MARKER');
    }
});

it('creates sm, md and thumb for a person photo', function () {
    $person = InvitationPerson::factory()->for($this->invitation)->create();
    $media = $this->uploader->attachImage($person, MediaCollection::Photo, mediaFixture('big.jpg'));
    expect(($this->dims)($media, 'sm')[0])->toBe(480)->and(($this->dims)($media, 'md')[0])->toBe(960);
    expect(($this->dims)($media, 'thumb'))->toBe([320, 320, 'image/webp']);
    expect($media->fresh()->hasGeneratedConversion('lg'))->toBeFalse();
});

it('does not convert a gift QR and keeps it as lossless transparent PNG', function () {
    $gift = InvitationGift::factory()->for($this->invitation)->create();
    $media = $this->uploader->attachImage($gift, MediaCollection::Qr, mediaFixture('transparent.png'));

    expect($media->mime_type)->toBe('image/png');
    expect($media->generated_conversions ?? [])->toBe([]);
    $png = imagecreatefrompng(Storage::disk('media')->path($media->getPathRelativeToRoot()));
    expect((imagecolorat($png, 2, 2) >> 24) & 127)->toBe(127);     // corner stays transparent
});

it('builds a srcset only from conversions that really exist', function () {
    $media = $this->uploader->attachImage($this->invitation, MediaCollection::Gallery, mediaFixture('big.jpg'));
    $full = MediaData::fromMedia($media->fresh());
    expect($full->srcset)->toContain('480w')->toContain('960w')->toContain('1600w')->and($full->width)->toBe(2400);

    // while the queue is still working, only some conversions exist: fall back instead of linking to 404s
    $partial = $media->fresh();
    $partial->generated_conversions = ['sm' => true];
    $partial->save();
    $data = MediaData::fromMedia($partial->fresh());
    expect($data->srcset)->toContain('480w')->not->toContain('960w');
    expect($data->url)->toBe($partial->getUrl());                  // md missing -> original
});

it('queues conversions when a real queue is configured', function () {
    config(['queue.default' => 'database']);
    Illuminate\Support\Facades\Queue::fake();
    $this->uploader->attachImage($this->invitation, MediaCollection::Gallery, mediaFixture('ok-800x600.jpg'));
    Queue::assertPushed(PerformConversionsJob::class);
})->skip(fn () => ! config('media-library.queue_conversions_by_default', true), 'conversions are configured non-queued');
