<?php

declare(strict_types=1);

namespace App\Livewire\Editor;

use App\Domain\Billing\Entitlements;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Media\Exceptions\InvalidMediaException;
use App\Domain\Media\Exceptions\MediaLimitReached;
use App\Domain\Media\MediaCollection;
use App\Domain\Media\Models\MusicTrack;
use App\Domain\Media\Services\MediaUploader;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * DSH-12 / DSH-13: gallery and music in the editor. Files go through MediaUploader (same path as the MED routes).
 * Method name "upload" is reserved by Livewire, so attach* is used instead.
 *
 * @property Invitation $invitation
 */
trait HandlesEditorMedia
{
    /** @var list<TemporaryUploadedFile> */
    public array $galleryPhotos = [];

    public mixed $musicFile = null;

    /** @var list<array{id:int,url:string,alt:string}> */
    public array $galleryItems = [];

    /** @var array{id:?int,url:?string,title:?string} */
    public array $musicItem = ['id' => null, 'url' => null, 'title' => null];

    /** @var array{source:string,track_id:?int,title:string} */
    public array $musicForm = ['source' => '', 'track_id' => null, 'title' => ''];

    /** @return array<string,string> */
    protected function messages(): array
    {
        $imageKb = intdiv((int) config('media-limits.image.max_bytes'), 1024);
        $audioKb = intdiv((int) config('media-limits.audio.max_bytes'), 1024);

        return [
            'galleryPhotos.*.max' => 'Berkas terlalu besar. Maksimal :max KB (sekitar '.intdiv($imageKb, 1024).' MB).',
            'galleryPhotos.max' => 'Berkas terlalu besar. Maksimal :max KB (sekitar '.intdiv($imageKb, 1024).' MB).',
            'musicFile.max' => 'Berkas terlalu besar. Maksimal :max KB (sekitar '.intdiv($audioKb, 1024).' MB).',
        ];
    }

    /** @return array<string,string> */
    protected function validationAttributes(): array
    {
        return [
            'galleryPhotos' => 'Foto galeri',
            'galleryPhotos.*' => 'Foto galeri',
            'musicFile' => 'Berkas musik',
        ];
    }

    public function updatedGalleryPhotos(): void
    {
        $this->attachGalleryPhotos();
    }

    public function updatedMusicFile(): void
    {
        $this->attachMusicFile();
    }

    public function attachGalleryPhotos(): void
    {
        $this->authorize('update', $this->invitation);
        $this->resetErrorBag(['galleryPhotos', 'galleryPhotos.*']);

        if ($this->galleryPhotos === []) {
            return;
        }

        $maxKb = intdiv((int) config('media-limits.image.max_bytes'), 1024);
        try {
            Validator::make(
                ['galleryPhotos' => $this->galleryPhotos],
                ['galleryPhotos' => ['required', 'array'], 'galleryPhotos.*' => ['required', 'file', "max:{$maxKb}"]],
                ['galleryPhotos.*.max' => 'Berkas terlalu besar. Maksimal :max KB (sekitar '.intdiv($maxKb, 1024).' MB).'],
                ['galleryPhotos' => 'Foto galeri', 'galleryPhotos.*' => 'Foto galeri'],
            )->validate();
        } catch (ValidationException $e) {
            foreach ($e->errors() as $key => $messages) {
                $this->addError($key, $messages[0]);
            }
            $this->reset('galleryPhotos');

            return;
        }

        $uploader = app(MediaUploader::class);
        $max = app(Entitlements::class)->limit($this->invitation, 'max_photos');
        $ok = 0;

        foreach ($this->galleryPhotos as $file) {
            if (! $file instanceof TemporaryUploadedFile) {
                continue;
            }
            try {
                $uploader->attachImage($this->invitation, MediaCollection::Gallery, $file, [], $max);
                $ok++;
            } catch (InvalidMediaException|MediaLimitReached $e) {
                $this->addError('galleryPhotos', $e->getMessage());
                break;
            }
        }

        $this->reset('galleryPhotos');
        $this->loadGalleryItems();
        if ($ok > 0) {
            $this->savedAt = now($this->invitation->timezone)->format('H.i');
            $this->dispatch('invitation-saved');
        }
    }

    public function removeGalleryItem(int $mediaId): void
    {
        $this->authorize('update', $this->invitation);
        $media = $this->invitation->media()->where('collection_name', 'gallery')->whereKey($mediaId)->first();
        if ($media === null) {
            return;
        }
        $media->delete();
        $this->loadGalleryItems();
        $this->savedAt = now($this->invitation->timezone)->format('H.i');
        $this->dispatch('invitation-saved');
    }

    /** @param  list<int|string>  $ids */
    public function reorderGallery(array $ids): void
    {
        $this->authorize('update', $this->invitation);
        $ids = array_values(array_map('intval', $ids));
        $own = $this->invitation->media()->where('collection_name', 'gallery')->whereIn('id', $ids)->count();
        if ($own !== count($ids) || $ids === []) {
            $this->addError('galleryPhotos', 'Urutan tidak valid.');

            return;
        }
        Media::setNewOrder($ids);
        $this->loadGalleryItems();
        $this->savedAt = now($this->invitation->timezone)->format('H.i');
        $this->dispatch('invitation-saved');
    }

    public function moveGalleryItem(int $mediaId, string $direction): void
    {
        $ids = array_column($this->galleryItems, 'id');
        $index = array_search($mediaId, $ids, true);
        if ($index === false) {
            return;
        }
        $swap = $direction === 'up' ? $index - 1 : $index + 1;
        if ($swap < 0 || $swap >= count($ids)) {
            return;
        }
        [$ids[$index], $ids[$swap]] = [$ids[$swap], $ids[$index]];
        $this->reorderGallery($ids);
    }

    public function attachMusicFile(): void
    {
        $this->authorize('update', $this->invitation);
        $this->resetErrorBag(['musicFile']);

        if (! app(Entitlements::class)->can($this->invitation, 'custom_music')) {
            $this->addError('musicFile', 'Unggah musik tersedia untuk paket tertentu.');
            $this->reset('musicFile');

            return;
        }

        if (! $this->musicFile instanceof TemporaryUploadedFile) {
            return;
        }

        $maxKb = intdiv((int) config('media-limits.audio.max_bytes'), 1024);
        try {
            Validator::make(
                ['musicFile' => $this->musicFile],
                ['musicFile' => ['required', 'file', "max:{$maxKb}"]],
                ['musicFile.max' => 'Berkas terlalu besar. Maksimal :max KB (sekitar '.intdiv($maxKb, 1024).' MB).'],
                ['musicFile' => 'Berkas musik'],
            )->validate();
        } catch (ValidationException $e) {
            $this->addError('musicFile', $e->errors()['musicFile'][0] ?? 'Berkas tidak valid.');
            $this->reset('musicFile');

            return;
        }

        try {
            app(MediaUploader::class)->attachAudio($this->invitation, MediaCollection::Music, $this->musicFile);
        } catch (InvalidMediaException $e) {
            $this->addError('musicFile', $e->getMessage());
            $this->reset('musicFile');

            return;
        }

        $this->reset('musicFile');
        $this->musicForm = [
            'source' => 'upload',
            'track_id' => null,
            'title' => $this->musicForm['title'] !== '' ? $this->musicForm['title'] : 'Musik latar',
        ];
        $this->persistMusicSettings();
        $this->loadMusicItem();
    }

    public function removeMusicFile(): void
    {
        $this->authorize('update', $this->invitation);
        $this->invitation->clearMediaCollection('music');
        if (($this->musicForm['source'] ?? '') === 'upload') {
            $this->musicForm['source'] = '';
            $this->persistMusicSettings();
        }
        $this->loadMusicItem();
        $this->savedAt = now($this->invitation->timezone)->format('H.i');
        $this->dispatch('invitation-saved');
    }

    public function updatedMusicFormSource(): void
    {
        $this->saveMusicSettings();
    }

    public function updatedMusicFormTrackId(): void
    {
        $this->saveMusicSettings();
    }

    public function updatedMusicFormTitle(): void
    {
        $this->saveMusicSettings();
    }

    public function saveMusicSettings(): void
    {
        $this->authorize('update', $this->invitation);
        $this->resetErrorBag(['musicForm', 'musicForm.source', 'musicForm.track_id', 'musicForm.title']);

        $source = is_string($this->musicForm['source'] ?? null) && $this->musicForm['source'] !== ''
            ? $this->musicForm['source']
            : null;
        $trackId = isset($this->musicForm['track_id']) && $this->musicForm['track_id'] !== '' && $this->musicForm['track_id'] !== null
            ? (int) $this->musicForm['track_id']
            : null;
        $title = trim((string) ($this->musicForm['title'] ?? ''));

        if ($source === 'upload' && ! app(Entitlements::class)->can($this->invitation, 'custom_music')) {
            $this->addError('musicForm.source', 'Unggah musik tersedia untuk paket tertentu.');

            return;
        }

        if ($source === 'catalog') {
            if (! config('media-limits.audio.catalog_enabled')) {
                $this->addError('musicForm.source', 'Katalog musik belum tersedia.');

                return;
            }
            if ($trackId === null || ! MusicTrack::query()->selectable()->whereKey($trackId)->exists()) {
                $this->addError('musicForm.track_id', 'Pilih lagu dari katalog.');

                return;
            }
        }

        if ($source !== null && ! in_array($source, ['upload', 'catalog'], true)) {
            $this->addError('musicForm.source', 'Sumber musik tidak valid.');

            return;
        }

        $this->musicForm = [
            'source' => $source ?? '',
            'track_id' => $source === 'catalog' ? $trackId : null,
            'title' => $title,
        ];
        $this->persistMusicSettings();
        // Keep the in-memory form (title can be set before a source is chosen); only refresh the file preview.
        $this->invitation->unsetRelation('media');
        $media = $this->invitation->getFirstMedia('music');
        $this->musicItem = [
            'id' => $media?->id,
            'url' => $media?->getUrl(),
            'title' => $title !== '' ? $title : ($media ? 'Musik latar' : null),
        ];
        $this->savedAt = now($this->invitation->timezone)->format('H.i');
        $this->dispatch('invitation-saved');
    }

    private function persistMusicSettings(): void
    {
        $settings = $this->invitation->settings ?? [];
        $source = is_string($this->musicForm['source'] ?? null) && $this->musicForm['source'] !== ''
            ? $this->musicForm['source']
            : null;

        if ($source === null) {
            unset($settings['music']);
        } else {
            $settings['music'] = array_filter([
                'source' => $source,
                'track_id' => $this->musicForm['track_id'],
                'title' => $this->musicForm['title'] !== '' ? $this->musicForm['title'] : null,
            ], fn (mixed $v) => $v !== null && $v !== '');
        }

        $this->invitation->forceFill(['settings' => $settings])->save();
    }

    private function loadGalleryItems(): void
    {
        $this->invitation->unsetRelation('media');
        $this->galleryItems = $this->invitation->getMedia('gallery')->map(fn (Media $m): array => [
            'id' => $m->id,
            'url' => $m->hasGeneratedConversion('thumb') ? $m->getUrl('thumb') : $m->getUrl(),
            'alt' => (string) ($m->getCustomProperty('alt') ?? ''),
        ])->values()->all();
    }

    private function loadMusicItem(): void
    {
        $this->invitation->unsetRelation('media');
        $media = $this->invitation->getFirstMedia('music');
        $stored = $this->invitation->fresh()->settings['music'] ?? [];
        $this->musicForm = [
            'source' => is_array($stored) ? (string) ($stored['source'] ?? '') : '',
            'track_id' => is_array($stored) && isset($stored['track_id']) ? (int) $stored['track_id'] : null,
            'title' => is_array($stored) ? (string) ($stored['title'] ?? '') : '',
        ];
        $this->musicItem = [
            'id' => $media?->id,
            'url' => $media?->getUrl(),
            'title' => $this->musicForm['title'] !== '' ? $this->musicForm['title'] : ($media ? 'Musik latar' : null),
        ];
    }

    private function bootMediaState(): void
    {
        $this->loadGalleryItems();
        $this->loadMusicItem();
    }
}
