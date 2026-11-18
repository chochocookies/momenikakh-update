<?php

declare(strict_types=1);

namespace App\Domain\Guest\Services;

/** Fills {nama}, {judul} and {tautan} in the WhatsApp message. Unknown placeholders are removed. */
final class ShareMessageBuilder
{
    private const FALLBACK = "Halo {nama}, ini undangan untuk Anda:\n{tautan}";

    public function __construct(private readonly string $defaultTemplate, private readonly int $max = 900) {}

    public function build(?string $template, string $guestName, string $title, string $url): string
    {
        $message = $this->fill($template !== null && trim($template) !== '' ? $template : $this->defaultTemplate, $guestName, $title, $url);
        if (mb_strlen($message) > $this->max) {
            $message = $this->fill(self::FALLBACK, $guestName, $title, $url); // never cut the link in half
        }

        return $message;
    }

    private function fill(string $template, string $name, string $title, string $url): string
    {
        $message = strtr($template, ['{nama}' => $name, '{judul}' => $title, '{tautan}' => $url]);

        return trim((string) preg_replace('/\{[a-z_]+\}/i', '', $message));
    }
}
