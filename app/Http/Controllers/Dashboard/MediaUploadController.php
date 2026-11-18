<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Domain\Billing\Entitlements;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Models\InvitationGift;
use App\Domain\Invitation\Models\InvitationPerson;
use App\Domain\Media\Exceptions\InvalidMediaException;
use App\Domain\Media\Exceptions\MediaLimitReached;
use App\Domain\Media\MediaCollection;
use App\Domain\Media\Services\MediaUploader;
use App\Http\Controllers\Controller;
use App\Http\Requests\Media\UploadMediaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class MediaUploadController extends Controller
{
    /** cover | gallery | music on the invitation itself. */
    public function store(UploadMediaRequest $request, Invitation $invitation, string $collection, MediaUploader $uploader, Entitlements $entitlements): JsonResponse
    {
        $target = $request->collection();
        abort_unless(in_array($target, [MediaCollection::Cover, MediaCollection::Gallery, MediaCollection::Music], true), 404);
        if ($target === MediaCollection::Music) {
            abort_unless($entitlements->can($invitation, 'custom_music'), 403, 'Unggah musik tersedia untuk paket tertentu.');
        }
        $max = $target === MediaCollection::Gallery ? $entitlements->limit($invitation, 'max_photos') : null;

        return $this->attach($uploader, $invitation, $target, $request, $max);
    }

    public function storePersonPhoto(UploadMediaRequest $request, Invitation $invitation, InvitationPerson $person, MediaUploader $uploader): JsonResponse
    {
        return $this->attach($uploader, $person, MediaCollection::Photo, $request);
    }

    public function storeGiftQr(UploadMediaRequest $request, Invitation $invitation, InvitationGift $gift, MediaUploader $uploader): JsonResponse
    {
        return $this->attach($uploader, $gift, MediaCollection::Qr, $request);
    }

    public function destroy(Request $request, Invitation $invitation, Media $media): JsonResponse
    {
        $this->authorize('update', $invitation);
        $media->loadMissing('model');
        $owner = $media->model;
        $belongs = match (true) {
            $owner instanceof Invitation => $owner->is($invitation),
            $owner instanceof InvitationPerson, $owner instanceof InvitationGift => (int) $owner->invitation_id === $invitation->id,
            default => false,
        };
        abort_unless($belongs, 404);
        $media->delete();

        return response()->json(null, 204);
    }

    public function reorderGallery(Request $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);
        $ids = array_map('intval', (array) $request->validate(['ids' => ['required', 'array', 'max:200'], 'ids.*' => ['integer']])['ids']);
        $own = $invitation->media()->where('collection_name', 'gallery')->whereIn('id', $ids)->count();
        abort_unless($own === count($ids), 422, 'Urutan tidak valid.');
        Media::setNewOrder($ids);

        return response()->json(['ok' => true]);
    }

    private function attach(MediaUploader $uploader, HasMedia $model, MediaCollection $collection, UploadMediaRequest $request, ?int $max = null): JsonResponse
    {
        try {
            $media = $collection->isImage()
                ? $uploader->attachImage($model, $collection, $request->file('file'), ['alt' => $request->input('alt')], $max)
                : $uploader->attachAudio($model, $collection, $request->file('file'));
        } catch (InvalidMediaException|MediaLimitReached $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => ['file' => [$e->getMessage()]]], 422);
        }

        return response()->json(['id' => $media->id, 'collection' => $collection->value, 'url' => $media->getUrl()], 201);
    }
}
