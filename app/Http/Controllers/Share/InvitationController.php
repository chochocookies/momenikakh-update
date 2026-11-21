<?php

declare(strict_types=1);

namespace App\Http\Controllers\Share;

use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Models\InvitationSlugAlias;
use App\Domain\Templates\InvitationViewModelBuilder;
use App\Domain\Templates\TemplateRenderer;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * The public invitation page: undangan.{domain}/{slug} (PUB-01, PUB-02). A published invitation is served; an expired one
 * answers 410 (it existed and is closed); a draft, an archived or an unknown slug answers the same 404, so the page never
 * tells a visitor which unpublished slugs exist. Caching and meta tags come with PUB-03 and PUB-08.
 */
final class InvitationController extends Controller
{
    /** Cache TTL for published invitations (PUB-03): 1 hour. */
    private const CACHE_TTL = 3600;

    /**
     * PUB-05: Redirect old slug aliases to the canonical invitation slug (301).
     */
    public function redirectAlias(string $slug): RedirectResponse
    {
        $alias = InvitationSlugAlias::query()->where('slug', $slug)->first();

        if ($alias && $alias->invitation->status === InvitationStatus::Published) {
            return redirect()->route('public.invitation', ['slug' => $alias->invitation->slug], 301);
        }

        // If no alias found, fall through to the main show() which will 404 appropriately
        abort(404);
    }

    public function show(string $slug, InvitationViewModelBuilder $builder, TemplateRenderer $renderer): Response
    {
        $invitation = Invitation::query()->where('slug', $slug)->first();

        // For non-published invitations, don't cache - return immediately with proper status
        if (! $invitation || $invitation->status !== InvitationStatus::Published) {
            return match ($invitation?->status) {
                InvitationStatus::Expired => response()->view('public.unavailable', [
                    'heading' => 'Undangan ini sudah berakhir',
                    'message' => 'Masa tayang undangan ini sudah selesai, jadi tautannya tidak lagi terbuka.',
                ], 410),
                default => response()->view('public.unavailable', [
                    'heading' => 'Undangan tidak ditemukan',
                    'message' => 'Tautannya mungkin salah ketik, atau undangannya belum atau tidak lagi dibagikan. Minta tautan yang benar dari pengirimnya.',
                ], 404),
            };
        }

        // Only cache published invitations (PUB-03)
        $cacheKey = "invitation:{$slug}";

        $html = Cache::store('redis')->remember($cacheKey, self::CACHE_TTL, function () use ($slug, $builder, $renderer) {
            $invitation = Invitation::query()->where('slug', $slug)->first();

            return $renderer->html($builder->fromModel($invitation));
        });

        return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function unavailable(int $status, string $heading, string $message): never
    {
        abort(response()->view('public.unavailable', ['heading' => $heading, 'message' => $message], $status));
    }
}
