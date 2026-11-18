<?php

declare(strict_types=1);

namespace App\Domain\Guest\Services;

/**
 * GST-02: builds the per-guest invitation link and the WhatsApp share URL.
 *   {public}/{slug}?to={name}&g={token}
 * `to` is only DISPLAYED by the page (client-side, textContent). `g` only lets the page report "opened".
 * Neither grants access to anything: the invitation itself is public to whoever holds the link.
 */
final class GuestLinkBuilder
{
    public const NAME_LIMIT = 60; // same limit the page applies when displaying the name

    public function __construct(private readonly string $baseUrl) {}

    public function plain(string $slug): string
    {
        return rtrim($this->baseUrl, '/').'/'.rawurlencode($slug);
    }

    public function forGuest(string $slug, string $guestName, ?string $token = null): string
    {
        $query = 'to='.rawurlencode($this->cleanName($guestName));
        if ($token !== null && $token !== '') {
            $query .= '&g='.rawurlencode($token);
        }

        return $this->plain($slug).'?'.$query;
    }

    /** wa.me link. With a phone it opens that chat; without one WhatsApp asks the sender to choose a contact. */
    public function whatsapp(?string $phone, string $message): string
    {
        $text = 'text='.rawurlencode($message);
        $phone = $phone === null ? '' : preg_replace('/\D+/', '', $phone);

        return $phone === '' || $phone === null
            ? 'https://wa.me/?'.$text
            : 'https://wa.me/'.$phone.'?'.$text;
    }

    public function cleanName(string $name): string
    {
        return mb_substr(TextCleaner::singleLine($name), 0, self::NAME_LIMIT);
    }
}
