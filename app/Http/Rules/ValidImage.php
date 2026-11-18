<?php

declare(strict_types=1);

namespace App\Http\Rules;

use App\Domain\Media\Services\ImageInspector;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/** MED-03 as a validation rule: real MIME, size, pixel count and minimum dimensions for the collection profile. */
final class ValidImage implements ValidationRule
{
    public function __construct(private readonly string $profile) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('Unggahan gagal. Coba lagi.');

            return;
        }
        $result = app(ImageInspector::class)->inspect((string) $value->getRealPath(), $this->profile);
        if (! $result->ok) {
            $fail($result->firstMessage() ?? 'Gambar tidak valid.');
        }
    }
}
