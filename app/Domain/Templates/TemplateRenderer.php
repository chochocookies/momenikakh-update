<?php

declare(strict_types=1);

namespace App\Domain\Templates;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

/**
 * Renders `templates::{slug}.index` with exactly two variables: the read-only view-model and the template's manifest.
 * Nothing else reaches a template, so it cannot query data or read the request.
 */
final class TemplateRenderer
{
    public function __construct(private readonly Factory $views, private readonly TemplateRegistry $templates) {}

    public function view(InvitationViewModel $invitation): View
    {
        return $this->views->make("templates::{$invitation->templateSlug}.index", $this->data($invitation));
    }

    public function html(InvitationViewModel $invitation): string
    {
        return $this->view($invitation)->render();
    }

    /** @return array{invitation:InvitationViewModel,template:TemplateManifest} */
    public function data(InvitationViewModel $invitation): array
    {
        return ['invitation' => $invitation, 'template' => $this->templates->get($invitation->templateSlug)];
    }

    /**
     * The view that renders a section: the template's own partial when it has one, else the shared partial.
     * Same order the Blade directive @invitationSection uses.
     *
     * @return string|null null when neither exists (a lint error, see templates:audit)
     */
    public function sectionView(string $slug, string $section): ?string
    {
        foreach (["templates::{$slug}.sections.{$section}", 'templates::'.TemplateRegistry::SHARED.".sections.{$section}"] as $name) {
            if ($this->views->exists($name)) {
                return $name;
            }
        }

        return null;
    }
}
