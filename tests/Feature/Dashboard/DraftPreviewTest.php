<?php

declare(strict_types=1);

use App\Domain\Invitation\Models\Invitation;
use App\Livewire\Editor\InvitationEditor;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\Support\Surfaces;

beforeEach(function () {
    $this->withoutVite();
    $this->owner = User::factory()->create();
    $this->stranger = User::factory()->create();
    $this->invitation = Invitation::factory()->for($this->owner)->wedding()->complete()->create(['title' => 'Raka & Sinta']);
});

it('renders the owner\'s draft with the real template and data, never cached or indexed, framable by the editor only', function () {
    $res = $this->actingAs($this->owner)->get(route('dashboard.invitations.preview', $this->invitation))->assertOk();
    $html = $res->getContent();

    expect($html)->toContain('data-theme="classic-elegant"')->toContain('Raka &amp; Sinta')->toContain('Raka Pratama')
        ->and($res->headers->get('X-Frame-Options'))->toBe('SAMEORIGIN')
        ->and($res->headers->get('Content-Security-Policy'))->toContain("frame-ancestors 'self'")
        ->and($res->headers->get('X-Robots-Tag'))->toBe('noindex, nofollow')
        ->and($res->headers->get('Cache-Control'))->toContain('no-store')
        // Preview mode: the guest forms are switched off.
        ->and($html)->toContain('Ini pratinjau');
});

it('shows a draft that is not published, which the public page would not', function () {
    $this->get(Surfaces::url('public', $this->invitation->slug))->assertNotFound();
    $this->actingAs($this->owner)->get(route('dashboard.invitations.preview', $this->invitation))->assertOk();
});

it('keeps other people out and visitors at the login page', function () {
    $this->get(route('dashboard.invitations.preview', $this->invitation))->assertRedirect(route('login'));
    $this->actingAs($this->stranger)->get(route('dashboard.invitations.preview', $this->invitation))->assertForbidden();
});

it('embeds the preview in the editor and sends the event that reloads it after a save, not after a failed one', function () {
    URL::forceRootUrl(Surfaces::url('app', ''));
    $editor = Livewire::actingAs($this->owner)->test(InvitationEditor::class, ['invitation' => $this->invitation]);

    $editor->assertSeeHtml('id="draft-preview"')->assertSeeHtml(route('dashboard.invitations.preview', $this->invitation));
    $editor->set('form.title', '')->assertNotDispatched('invitation-saved');
    $editor->set('form.title', 'Judul baru')->assertDispatched('invitation-saved');
});
