<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;

it('serves media URLs from the CDN host when the media disk has a CDN url', function () {
    config(['filesystems.disks.media_cdn_test' => [
        'driver' => 's3', 'key' => 'k', 'secret' => 's', 'region' => 'auto', 'bucket' => 'momenikah-media',
        'endpoint' => 'https://storage.example.test', 'use_path_style_endpoint' => true,
        'url' => 'https://cdn.example.test', 'throw' => true,
    ]]);
    $url = Storage::disk('media_cdn_test')->url('3f/3fa85f64-5717-4562-b3fc-2c963f66afa6/conversions/foto-md.webp');
    expect($url)->toStartWith('https://cdn.example.test/')->and($url)->not->toContain('storage.example.test');
});

it('has the media disk defined with long-lived cache headers and no ACL', function () {
    $disk = config('filesystems.disks.media');
    expect($disk)->not->toBeNull();
    if (($disk['driver'] ?? '') === 's3') {
        expect($disk['options']['CacheControl'] ?? '')->toContain('immutable');
        expect($disk)->not->toHaveKey('visibility'); // R2 and many S3-compatible stores reject ACLs
    }
});
