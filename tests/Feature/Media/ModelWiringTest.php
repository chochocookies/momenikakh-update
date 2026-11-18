<?php

declare(strict_types=1);

// Diagnostics: fails with a clear message if the Media Library patch (INSTALL.md step 5) is missing.

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Models\InvitationGift;
use App\Domain\Invitation\Models\InvitationPerson;
use App\Domain\Media\Models\MusicTrack;
use App\Domain\Media\UuidPathGenerator;
use Spatie\MediaLibrary\HasMedia;

it('wires media onto the models', function (string $model, array $collections) {
    expect(is_subclass_of($model, HasMedia::class))->toBeTrue("{$model} must implement HasMedia");
    $instance = new $model;
    $instance->registerMediaCollections();
    $registered = collect($instance->mediaCollections)->pluck('name')->all();
    foreach ($collections as $c) {
        expect($registered)->toContain($c);
    }
})->with([
    'invitation' => [Invitation::class, ['cover', 'gallery', 'music']],
    'person' => [InvitationPerson::class, ['photo']],
    'gift' => [InvitationGift::class, ['qr']],
    'music track' => [MusicTrack::class, ['audio']],
]);

it('uses the uuid path generator', function () {
    expect(config('media-library.path_generator'))->toBe(UuidPathGenerator::class);
});
