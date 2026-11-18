<?php

declare(strict_types=1);

namespace App\Http\Requests\Media;

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Media\MediaCollection;
use App\Http\Rules\ValidAudio;
use App\Http\Rules\ValidImage;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Used by every upload route. The collection comes from the route ({collection}) or defaults to the route's
 * `collection` action default (person photo -> photo, gift qr -> qr).
 */
final class UploadMediaRequest extends FormRequest
{
    public function collection(): MediaCollection
    {
        return MediaCollection::tryFrom((string) ($this->route('collection') ?? $this->route()->defaults['collection'] ?? '')) ?? MediaCollection::Gallery;
    }

    public function authorize(): bool
    {
        $invitation = $this->route('invitation');

        return $invitation instanceof Invitation && ($this->user()?->can('update', $invitation) ?? false);
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $collection = $this->collection();
        $maxKb = intdiv((int) config($collection->isImage() ? 'media-limits.image.max_bytes' : 'media-limits.audio.max_bytes'), 1024);

        return [
            'file' => ['required', 'file', "max:{$maxKb}", $collection->isImage() ? new ValidImage($collection->value) : new ValidAudio],
            'alt' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function attributes(): array
    {
        return ['file' => 'Berkas', 'alt' => 'Teks alternatif'];
    }
}
