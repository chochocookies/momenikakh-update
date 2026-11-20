<?php

declare(strict_types=1);

namespace App\Http\Controllers\Share;

use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Templates\InvitationViewModelBuilder;
use App\Domain\Templates\TemplateRenderer;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * The public invitation page: undangan.{domain}/{slug} (PUB-01, PUB-02). A published invitation is served; an expired one
 * answers 410 (it existed and is closed); a draft, an archived or an unknown slug answers the same 404, so the page never
 * tells a visitor which unpublished slugs exist. Caching and meta tags come with PUB-03 and PUB-08.
 */
final class InvitationController extends Controller
{
    public function show(string $slug, InvitationViewModelBuilder $builder, TemplateRenderer $renderer): View
    {
        $invitation = Invitation::query()->where('slug', $slug)->first();

        return match ($invitation?->status) {
            InvitationStatus::Published => $renderer->view($builder->fromModel($invitation)),
            InvitationStatus::Expired => $this->unavailable(410, 'Undangan ini sudah berakhir', 'Masa tayang undangan ini sudah selesai, jadi tautannya tidak lagi terbuka.'),
            default => $this->unavailable(404, 'Undangan tidak ditemukan', 'Tautannya mungkin salah ketik, atau undangannya belum atau tidak lagi dibagikan. Minta tautan yang benar dari pengirimnya.'),
        };
    }

    private function unavailable(int $status, string $heading, string $message): never
    {
        abort(response()->view('public.unavailable', ['heading' => $heading, 'message' => $message], $status));
    }
}
