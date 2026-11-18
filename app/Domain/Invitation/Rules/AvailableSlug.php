<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Rules;

use App\Domain\Invitation\Services\SlugGenerator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Validation rule for user-chosen slugs (F4-06). Pass the invitation id when editing. */
final class AvailableSlug implements ValidationRule
{
    public function __construct(private readonly ?int $ignoreId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! app(SlugGenerator::class)->isAvailable($value, $this->ignoreId)) {
            $fail('Alamat undangan ini tidak tersedia. Coba yang lain.');
        }
    }
}
