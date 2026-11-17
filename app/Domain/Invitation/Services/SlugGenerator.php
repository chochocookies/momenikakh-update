<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Services;

use App\Domain\Invitation\Models\Invitation;
use Illuminate\Support\Str;
use RuntimeException;

/** DOM-07: "raka-sinta-x7k2": readable name + 4 random characters, unique, never reserved. */
final class SlugGenerator
{
    /** No 0/o/1/i/l: easier to read aloud and type from a screenshot. */
    private const ALPHABET = 'abcdefghjkmnpqrstuvwxyz23456789';
    private const FORMAT = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /** @param list<string>|null $reserved */
    public function __construct(private readonly ?array $reserved = null)
    {
    }

    public function generate(string $title): string
    {
        $base = $this->base($title);
        for ($attempt = 0; $attempt < 12; $attempt++) {
            $slug = $base.'-'.$this->suffix();
            if ($this->isAvailable($slug)) {
                return $slug;
            }
        }

        throw new RuntimeException('Could not generate a unique invitation slug.');
    }

    public function isReserved(string $slug): bool
    {
        $list = $this->reserved ?? (array) config('reserved-slugs', []);

        return in_array(strtolower($slug), $list, true);
    }

    /** Valid format, not reserved, and not taken (soft-deleted invitations still hold their slug). */
    public function isAvailable(string $slug, ?int $ignoreId = null): bool
    {
        if (strlen($slug) < 3 || strlen($slug) > 60 || preg_match(self::FORMAT, $slug) !== 1) {
            return false;
        }
        if ($this->isReserved($slug)) {
            return false;
        }

        return ! Invitation::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }

    private function base(string $title): string
    {
        $slug = Str::slug($title);
        if ($slug === '') {
            return 'undangan';
        }
        if (strlen($slug) > 40) {
            $cut = substr($slug, 0, 40);
            $slug = str_contains($cut, '-') ? substr($cut, 0, (int) strrpos($cut, '-')) : $cut;
        }

        return $slug;
    }

    private function suffix(): string
    {
        $out = '';
        for ($i = 0; $i < 4; $i++) {
            $out .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return $out;
    }
}
