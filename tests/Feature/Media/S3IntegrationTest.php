<?php

declare(strict_types=1);

use App\Domain\Media\MediaCollection;
use App\Domain\Media\Services\MediaUploader;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

require_once __DIR__.'/MediaTestCase.php';

/*
 * Real bucket test (MinIO locally, R2/S3 on staging). Skipped unless MEDIA_S3_TESTS=1.
 *   MEDIA_S3_TESTS=1 MEDIA_DRIVER=s3 php artisan test --filter=S3Integration
 */
beforeEach(function () {
    config(['media-limits.disk' => 'media', 'media-library.disk_name' => 'media']);
    [$this->owner, $this->invitation] = newOwnerWithInvitation();
});

it('uploads into the bucket, converts, and serves the URL through the CDN host', function () {
    $media = app(MediaUploader::class)->attachImage($this->invitation, MediaCollection::Cover, mediaFixture('big.jpg'));

    expect(Storage::disk('media')->exists($media->getPathRelativeToRoot()))->toBeTrue();
    expect(Storage::disk('media')->exists($media->getPathRelativeToRoot('md')))->toBeTrue();

    $cdn = config('media-limits.cdn_url');
    if ($cdn) {
        expect($media->getUrl('md'))->toStartWith(rtrim((string) $cdn, '/').'/');
        $head = Http::head($media->getUrl('md'));
        expect($head->status())->toBe(200);
        expect($head->header('Content-Type'))->toContain('image/webp');
        expect($head->header('Cache-Control'))->toContain('immutable');
    }
    $media->delete();
    expect(Storage::disk('media')->exists($media->getPathRelativeToRoot()))->toBeFalse();
})->skip(fn () => ! env('MEDIA_S3_TESTS'), 'set MEDIA_S3_TESTS=1 to run against a real bucket');
