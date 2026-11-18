<?php

declare(strict_types=1);

use App\Domain\Media\UuidPathGenerator;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

it('lays files out by random uuid, never by numeric id', function () {
    $media = new Media;
    $media->id = 42;
    $media->uuid = '3fa85f64-5717-4562-b3fc-2c963f66afa6';
    $gen = new UuidPathGenerator;

    expect($gen->getPath($media))->toBe('3f/3fa85f64-5717-4562-b3fc-2c963f66afa6/');
    expect($gen->getPathForConversions($media))->toBe('3f/3fa85f64-5717-4562-b3fc-2c963f66afa6/conversions/');
    expect($gen->getPathForResponsiveImages($media))->toBe('3f/3fa85f64-5717-4562-b3fc-2c963f66afa6/responsive-images/');
    expect($gen->getPath($media))->not->toContain('42');
});

it('produces different, unguessable directories for different media', function () {
    $gen = new UuidPathGenerator;
    $paths = [];
    foreach (range(1, 50) as $i) {
        $m = new Media;
        $m->id = $i;
        $m->uuid = (string) Str::uuid();
        $paths[] = $gen->getPath($m);
    }
    expect(array_unique($paths))->toHaveCount(50);
    foreach ($paths as $p) {
        expect($p)->toMatch('#^[0-9a-f]{2}/[0-9a-f-]{36}/$#');
    }
});
