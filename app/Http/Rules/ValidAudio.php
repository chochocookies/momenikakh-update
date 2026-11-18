<?php

declare(strict_types=1);

namespace App\Http\Rules;

use App\Domain\Media\Services\AudioInspector;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

final class ValidAudio implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('Unggahan gagal. Coba lagi.');

            return;
        }
        $result = app(AudioInspector::class)->inspect((string) $value->getRealPath(), $value->getClientOriginalName());
        if (! $result->ok) {
            $fail($result->firstMessage() ?? 'Berkas musik tidak valid.');
        }
    }
}
