<?php

declare(strict_types=1);

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Media\MediaCollection;
use App\Domain\Media\Services\MediaUploader;
use App\Domain\Media\Services\OrphanPruner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

require_once __DIR__.'/MediaTestCase.php';

beforeEach(function () {
    useFakeMediaDisk();
    [$this->owner, $this->invitation] = newOwnerWithInvitation();
    $this->attach = fn (Invitation $i) => app(MediaUploader::class)->attachImage($i, MediaCollection::Gallery, mediaFixture('ok-800x600.jpg'));
    $this->pruner = fn () => new OrphanPruner('media', 24);
    $this->oldDir = function (): string {
        $dir = substr($u = (string) Str::uuid(), 0, 2).'/'.$u;
        Storage::disk('media')->put($dir.'/file.webp', 'x');
        touch(Storage::disk('media')->path($dir.'/file.webp'), now()->subDays(3)->getTimestamp());

        return $dir;
    };
});

it('never deletes media that is still in use', function () {
    $media = ($this->attach)($this->invitation);
    $this->travel(10)->days();

    $report = ($this->pruner)()->run(dryRun: false);

    expect($report->orphanRowIds)->toBeEmpty()->and($report->orphanDirectories)->toBeEmpty();
    expect(Media::query()->whereKey($media->id)->exists())->toBeTrue();
    expect(Storage::disk('media')->exists($media->getPathRelativeToRoot()))->toBeTrue();
});

it('keeps media of a soft-deleted invitation (it can still be restored)', function () {
    $media = ($this->attach)($this->invitation);
    $this->invitation->delete();
    $this->travel(10)->days();

    expect(($this->pruner)()->run(false)->orphanRowIds)->toBeEmpty();
    expect(Media::query()->whereKey($media->id)->exists())->toBeTrue();
});

it('deletes rows and files whose owner is really gone, after the grace period', function () {
    $media = ($this->attach)($this->invitation);
    $path = $media->getPathRelativeToRoot();
    DB::table('invitations')->where('id', $this->invitation->id)->delete();   // hard delete without model events

    // inside the grace period: untouched
    expect(($this->pruner)()->run(false)->orphanRowIds)->toBeEmpty();
    expect(Media::query()->whereKey($media->id)->exists())->toBeTrue();

    $this->travel(25)->hours();
    $report = ($this->pruner)()->run(false);

    expect($report->orphanRowIds)->toBe([$media->id]);
    expect(Media::query()->whereKey($media->id)->exists())->toBeFalse();
    expect(Storage::disk('media')->exists($path))->toBeFalse();
});

it('reports but does not delete during a dry run', function () {
    $media = ($this->attach)($this->invitation);
    DB::table('invitations')->where('id', $this->invitation->id)->delete();
    $this->travel(25)->hours();

    $report = ($this->pruner)()->run(dryRun: true);

    expect($report->orphanRowIds)->toBe([$media->id])->and($report->dryRun)->toBeTrue();
    expect(Media::query()->whereKey($media->id)->exists())->toBeTrue();
});

it('removes old storage folders that have no media row, but not recent ones', function () {
    $old = ($this->oldDir)();
    $recent = substr($u = (string) Str::uuid(), 0, 2).'/'.$u;
    Storage::disk('media')->put($recent.'/file.webp', 'x');

    $report = ($this->pruner)()->run(false);

    expect($report->orphanDirectories)->toBe([$old]);
    expect(Storage::disk('media')->exists($old.'/file.webp'))->toBeFalse();
    expect(Storage::disk('media')->exists($recent.'/file.webp'))->toBeTrue();   // might be an upload in progress
});

it('never touches folders outside its own layout', function () {
    Storage::disk('media')->put('livewire-tmp/abc/file.tmp', 'x');
    touch(Storage::disk('media')->path('livewire-tmp/abc/file.tmp'), now()->subDays(9)->getTimestamp());
    Storage::disk('media')->put('zz/not-a-uuid/file.webp', 'x');
    touch(Storage::disk('media')->path('zz/not-a-uuid/file.webp'), now()->subDays(9)->getTimestamp());

    expect(($this->pruner)()->run(false)->orphanDirectories)->toBeEmpty();
    expect(Storage::disk('media')->exists('livewire-tmp/abc/file.tmp'))->toBeTrue();
});

it('leaves media with an unresolvable owner type alone and reports it', function () {
    $media = ($this->attach)($this->invitation);
    DB::table('media')->where('id', $media->id)->update(['model_type' => 'App\\Removed\\Thing', 'created_at' => now()->subDays(5)]);

    $report = ($this->pruner)()->run(false);

    expect($report->orphanRowIds)->toBeEmpty()->and($report->unverifiable)->toBe(['App\\Removed\\Thing']);
    expect(Media::query()->whereKey($media->id)->exists())->toBeTrue();
});

it('runs from the console and the schedule', function () {
    $this->artisan('media:prune-orphans --dry-run')->expectsOutputToContain('Would delete')->assertSuccessful();
    $this->artisan('media:prune-orphans --grace=1')->expectsOutputToContain('Deleted')->assertSuccessful();
    $this->artisan('schedule:list')->expectsOutputToContain('media:prune-orphans')->assertSuccessful();
});
