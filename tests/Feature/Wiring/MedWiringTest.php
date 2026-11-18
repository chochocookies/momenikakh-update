<?php

declare(strict_types=1);

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Media\Services\OrphanPruner;
use App\Domain\Media\UuidPathGenerator;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\Support\WiringFixtures as Fx;

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('media');
    [$this->user, $this->invitation] = Fx::owned();
});

function uploadTo(string $name, array $params, $file, ?User $as = null)
{
    return test()->actingAs($as ?? test()->user)->postJson(route($name, $params), ['file' => $file]);
}

it('uses the media disk, the uuid path layout and registers the media routes', function () {
    expect(config('media-library.disk_name'))->toBe('media')
        ->and(config('media-library.path_generator'))->toBe(UuidPathGenerator::class)
        ->and(config('filesystems.disks.media'))->toBeArray();
    foreach (['store', 'reorder', 'destroy', 'person-photo', 'gift-qr'] as $n) {
        expect(Route::has("dashboard.media.{$n}"))->toBeTrue("missing dashboard.media.{$n}");
    }
});

it('stores a cover as a clean, EXIF-free WebP under a random uuid path', function () {
    $res = uploadTo('dashboard.media.store', [$this->invitation, 'cover'], Fx::upload(Fx::jpeg(1300, 700), 'IMG_2024 (1).jpg'));
    $res->assertCreated()->assertJsonStructure(['id', 'collection', 'url']);

    $media = Media::query()->findOrFail($res->json('id'));
    expect($media->collection_name)->toBe('cover')->and($media->mime_type)->toBe('image/webp')->and($media->file_name)->toMatch('/^[0-9a-f-]{36}\.webp$/')
        ->and($media->file_name)->not->toContain('IMG_2024')
        ->and($media->uuid)->toMatch('/^[0-9a-f-]{36}$/');
    $path = substr($media->uuid, 0, 2).'/'.$media->uuid.'/'.$media->file_name;
    Storage::disk('media')->assertExists($path);

    $bytes = Storage::disk('media')->get($path);
    foreach ([Fx::MARK_EXIF, Fx::MARK_COMMENT, Fx::MARK_POLYGLOT, 'Exif'] as $needle) {
        expect(str_contains($bytes, $needle))->toBeFalse("stored file still contains {$needle}");
    }
    expect($media->getCustomProperty('width'))->toBe(1300);
});

it('generates the 480/960/1600, thumb and og conversions for a cover', function () {
    $res = uploadTo('dashboard.media.store', [$this->invitation, 'cover'], Fx::upload(Fx::jpeg(2000, 1200, 1, false), 'a.jpg'));
    $media = Media::query()->findOrFail($res->json('id'))->fresh();

    foreach (['sm', 'md', 'lg', 'thumb', 'og'] as $conversion) {
        expect($media->hasGeneratedConversion($conversion))->toBeTrue("conversion {$conversion} not generated");
    }
    [$w] = getimagesizefromstring(Storage::disk('media')->get(substr($media->uuid, 0, 2).'/'.$media->uuid.'/conversions/'.pathinfo($media->file_name, PATHINFO_FILENAME).'-md.webp'));
    expect($w)->toBe(960);
    $og = getimagesizefromstring(Storage::disk('media')->get(substr($media->uuid, 0, 2).'/'.$media->uuid.'/conversions/'.pathinfo($media->file_name, PATHINFO_FILENAME).'-og.jpg'));
    expect([$og[0], $og[1], $og['mime']])->toBe([1200, 630, 'image/jpeg']);
});

it('applies EXIF orientation before stripping it', function () {
    // 1300x700 stored landscape with orientation 6 (rotate 90 CW) must come out portrait 700x1300
    $res = uploadTo('dashboard.media.store', [$this->invitation, 'gallery'], Fx::upload(Fx::jpeg(1300, 700, 6), 'p.jpg'));
    $res->assertCreated();
    $media = Media::query()->findOrFail($res->json('id'));
    expect([$media->getCustomProperty('width'), $media->getCustomProperty('height')])->toBe([700, 1300]);
});

it('rejects fake and unsupported uploads with 422 and stores nothing', function () {
    $bad = [
        'php named jpg' => Fx::upload('<?php system($_GET["c"]); ?>', 'shell.jpg'),
        'svg named png' => Fx::upload(Fx::svg(), 'logo.png'),
        'gif' => Fx::upload(Fx::gif(), 'a.gif'),
        'empty' => Fx::upload('', 'a.jpg'),
        'too small' => Fx::upload(Fx::jpeg(300, 200), 'tiny.jpg'),
    ];
    foreach ($bad as $label => $file) {
        uploadTo('dashboard.media.store', [$this->invitation, 'cover'], $file)->assertUnprocessable();
    }
    expect(Media::query()->count())->toBe(0)->and(Storage::disk('media')->allFiles())->toBe([]);
});

it('rejects a decompression bomb from the header without decoding it', function () {
    // 30000 x 30000 px PNG header only (IHDR) is enough for getimagesize
    $png = "\x89PNG\r\n\x1a\n".pack('N', 13).'IHDR'.pack('NN', 30000, 30000)."\x08\x06\x00\x00\x00".pack('N', crc32('IHDR'.pack('NN', 30000, 30000)."\x08\x06\x00\x00\x00")).pack('N', 0).'IEND'."\xAE\x42\x60\x82";
    uploadTo('dashboard.media.store', [$this->invitation, 'gallery'], Fx::upload($png, 'bomb.png'))->assertUnprocessable();
    expect(Media::query()->count())->toBe(0);
});

it('keeps the cover single and enforces the gallery limit from Entitlements', function () {
    uploadTo('dashboard.media.store', [$this->invitation, 'cover'], Fx::upload(Fx::jpeg(1300, 700), 'a.jpg'))->assertCreated();
    uploadTo('dashboard.media.store', [$this->invitation, 'cover'], Fx::upload(Fx::jpeg(1400, 800), 'b.jpg'))->assertCreated();
    expect($this->invitation->media()->where('collection_name', 'cover')->count())->toBe(1);

    config(['entitlements.free.limits.max_photos' => 2]);
    foreach (range(1, 2) as $i) {
        uploadTo('dashboard.media.store', [$this->invitation, 'gallery'], Fx::upload(Fx::jpeg(800, 600, 1, false), "g{$i}.jpg"))->assertCreated();
    }
    uploadTo('dashboard.media.store', [$this->invitation, 'gallery'], Fx::upload(Fx::jpeg(800, 600, 1, false), 'g3.jpg'))->assertUnprocessable();
    expect($this->invitation->media()->where('collection_name', 'gallery')->count())->toBe(2);
});

it('forbids uploads, deletes and reorders by other users', function () {
    $stranger = User::factory()->create();
    uploadTo('dashboard.media.store', [$this->invitation, 'cover'], Fx::upload(Fx::jpeg(), 'a.jpg'), $stranger)->assertForbidden();

    $id = uploadTo('dashboard.media.store', [$this->invitation, 'gallery'], Fx::upload(Fx::jpeg(800, 600, 1, false), 'a.jpg'))->json('id');
    $this->actingAs($stranger)->deleteJson(route('dashboard.media.destroy', [$this->invitation, $id]))->assertForbidden();

    // my own invitation URL must not let me delete someone else's media
    [$other, $otherInv] = Fx::owned();
    $foreign = uploadTo('dashboard.media.store', [$otherInv, 'gallery'], Fx::upload(Fx::jpeg(800, 600, 1, false), 'b.jpg'), $other)->json('id');
    $this->actingAs($this->user)->deleteJson(route('dashboard.media.destroy', [$this->invitation, $foreign]))->assertNotFound();
    $this->actingAs($this->user)->putJson(route('dashboard.media.reorder', $this->invitation), ['ids' => [$foreign]])->assertUnprocessable();
    expect(Media::query()->whereKey($foreign)->exists())->toBeTrue();
});

it('deletes media with its files and reorders the gallery', function () {
    $ids = collect(range(1, 3))->map(fn ($i) => uploadTo('dashboard.media.store', [$this->invitation, 'gallery'], Fx::upload(Fx::jpeg(800, 600, 1, false), "g{$i}.jpg"))->json('id'))->all();

    $this->actingAs($this->user)->putJson(route('dashboard.media.reorder', $this->invitation), ['ids' => array_reverse($ids)])->assertOk();
    expect($this->invitation->media()->where('collection_name', 'gallery')->orderBy('order_column')->pluck('id')->all())->toBe(array_reverse($ids));

    $media = Media::query()->findOrFail($ids[0]);
    $dir = substr($media->uuid, 0, 2).'/'.$media->uuid;
    $this->actingAs($this->user)->deleteJson(route('dashboard.media.destroy', [$this->invitation, $ids[0]]))->assertNoContent();
    expect(Media::query()->whereKey($ids[0])->exists())->toBeFalse();
    Storage::disk('media')->assertMissing($dir.'/'.$media->file_name);
});

it('stores a person photo and a gift QR (lossless PNG, no conversions), scoped to the invitation', function () {
    $person = $this->invitation->people()->create(['role' => 'groom', 'name' => 'Budi', 'sort' => 1]);
    $gift = $this->invitation->gifts()->create(['type' => 'bank', 'provider' => 'BCA', 'holder_name' => 'Budi', 'number' => '1234567890', 'is_active' => true, 'sort' => 1]);

    $res = uploadTo('dashboard.media.person-photo', [$this->invitation, $person], Fx::upload(Fx::jpeg(800, 800, 1, false), 'me.jpg'));
    $res->assertCreated()->assertJsonPath('collection', 'photo');
    expect($person->fresh()->getFirstMedia('photo')->mime_type)->toBe('image/webp');

    $res = uploadTo('dashboard.media.gift-qr', [$this->invitation, $gift], Fx::upload(Fx::png(400, 400, true), 'qr.png'));
    $res->assertCreated()->assertJsonPath('collection', 'qr');
    $qr = $gift->fresh()->getFirstMedia('qr');
    expect($qr->mime_type)->toBe('image/png')->and((array) $qr->generated_conversions)->toBe([]);

    [, $otherInv] = Fx::owned();
    $foreignPerson = $otherInv->people()->create(['role' => 'bride', 'name' => 'Siti', 'sort' => 1]);
    uploadTo('dashboard.media.person-photo', [$this->invitation, $foreignPerson], Fx::upload(Fx::jpeg(800, 800, 1, false), 'x.jpg'))->assertNotFound();
});

it('accepts mp3/ogg music only with the custom_music ability and checks content, not the name', function () {
    uploadTo('dashboard.media.store', [$this->invitation, 'music'], Fx::upload(Fx::mp3(), 'lagu.mp3'))->assertForbidden(); // free plan

    config(['entitlements.free.features.custom_music' => true]);
    $res = uploadTo('dashboard.media.store', [$this->invitation, 'music'], Fx::upload(Fx::mp3(), 'lagu.mp3'));
    $res->assertCreated()->assertJsonPath('collection', 'music');
    expect($this->invitation->getFirstMedia('music')->file_name)->toMatch('/^[0-9a-f-]{36}\.mp3$/');

    uploadTo('dashboard.media.store', [$this->invitation, 'music'], Fx::upload(Fx::ogg(), 'lagu.ogg'))->assertCreated();
    uploadTo('dashboard.media.store', [$this->invitation, 'music'], Fx::upload('<?php echo 1;', 'lagu.mp3'))->assertUnprocessable();
    uploadTo('dashboard.media.store', [$this->invitation, 'music'], Fx::upload(Fx::ogg(), 'lagu.mp3'))->assertUnprocessable();
    uploadTo('dashboard.media.store', [$this->invitation, 'music'], Fx::upload(Fx::mp3(), 'lagu.wav'))->assertUnprocessable();
});

it('prunes orphan rows and folders only after the grace period and never touches live media', function () {
    $live = uploadTo('dashboard.media.store', [$this->invitation, 'gallery'], Fx::upload(Fx::jpeg(800, 600, 1, false), 'live.jpg'))->json('id');
    $soft = Invitation::factory()->for($this->user)->create();
    $softId = uploadTo('dashboard.media.store', [$soft, 'gallery'], Fx::upload(Fx::jpeg(800, 600, 1, false), 'soft.jpg'))->json('id');
    $soft->delete(); // soft delete: owner still "exists" (restorable)

    // an orphan row: media whose owner row was removed straight from the database
    $ghost = $this->invitation->addMedia(Fx::upload(Fx::jpeg(800, 600, 1, false), 'g.jpg')->getRealPath())->preservingOriginal()->toMediaCollection('gallery', 'media');
    DB::table('invitations')->where('id', $this->invitation->id)->delete();
    $stray = 'ab/'.Str::uuid();
    Storage::disk('media')->put("{$stray}/file.webp", 'x');
    Storage::disk('media')->put('livewire-tmp/keep.txt', 'x');

    $pruner = new OrphanPruner('media', 24);
    $report = $pruner->run(true);
    expect($report->orphanRowIds)->toBe([])->and($report->orphanDirectories)->toBe([]); // inside the grace period

    $this->travel(25)->hours();
    $report = $pruner->run(true);
    expect($report->orphanRowIds)->toContain($ghost->id)->and($report->orphanRowIds)->not->toContain($softId)->and($report->orphanRowIds)->not->toContain($live === $ghost->id ? 0 : -1)
        ->and($report->orphanDirectories)->toBe([$stray]);
    Storage::disk('media')->assertExists("{$stray}/file.webp"); // dry run deletes nothing

    $pruner->run(false);
    expect(Media::query()->whereKey($ghost->id)->exists())->toBeFalse()->and(Media::query()->whereKey($softId)->exists())->toBeTrue();
    Storage::disk('media')->assertMissing("{$stray}/file.webp");
    Storage::disk('media')->assertExists('livewire-tmp/keep.txt');
});
