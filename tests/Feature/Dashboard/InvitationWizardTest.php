<?php

declare(strict_types=1);

use App\Domain\Billing\Entitlements;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Services\InvitationLifecycle;
use App\Domain\Invitation\Services\PublishRequiresPlan;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Tests\Support\DesignSystem;

beforeEach(function () {
    $this->withoutVite();
    $this->user = User::factory()->create();
    $this->seed(PlanSeeder::class);
    $this->store = fn (array $data) => $this->actingAs($this->user)->post(route('dashboard.invitations.store'), $data);
    $this->valid = ['event_type' => 'wedding', 'template_slug' => 'classic-elegant', 'title' => 'Raka & Sinta', 'start' => 'sample'];
});

it('step 1 lists the event types that have a template and says the rest come later', function () {
    $res = $this->actingAs($this->user)->get(route('dashboard.invitations.create'))->assertOk();
    $xp = DesignSystem::xpath($res->getContent());

    $labels = [];
    foreach ($xp->query('//ul[@aria-label="Jenis acara"]//h2') as $h) {
        $labels[] = $h->textContent;
    }
    expect($labels)->toBe(['Pernikahan', 'Walimatul Urs', 'Tunangan'])
        ->and($res->getContent())->toContain('Langkah 1 dari 2')->toContain('Jenis acara lain menyusul.')
        ->and($xp->query('//a[contains(@href,"event=wedding")]')->length)->toBe(1);
});

it('step 2 shows the templates of the event type with a preview link and the start choices', function () {
    $res = $this->actingAs($this->user)->get(route('dashboard.invitations.create', ['event' => 'wedding']))->assertOk();
    $xp = DesignSystem::xpath($res->getContent());

    expect($res->getContent())->toContain('Langkah 2 dari 2')->toContain('Classic Elegant')
        ->and($xp->query('//input[@type="radio"][@name="template_slug"][@value="classic-elegant"][@checked]')->length)->toBe(1)
        ->and($xp->query('//input[@type="hidden"][@name="event_type"][@value="wedding"]')->length)->toBe(1)
        ->and($xp->query('//input[@name="start"][@value="sample"][@checked]')->length)->toBe(1)
        ->and($xp->query('//input[@name="start"][@value="blank"]')->length)->toBe(1)
        ->and($xp->query('//input[@name="title"]')->length)->toBe(1)
        ->and($xp->query('//a[@target="_blank"][contains(@href,"/preview/classic-elegant")]')->length)->toBe(1)
        ->and($xp->query('//img[contains(@src,"classic-elegant/thumb.webp")][@alt][@width][@height]')->length)->toBe(1);
});

it('step 2 answers 404 for an unknown event type or one without a template', function (string $event) {
    $this->actingAs($this->user)->get(route('dashboard.invitations.create', ['event' => $event]))->assertNotFound();
})->with(['funeral', 'birthday', 'aqiqah', '']);

it('creates a draft with the sample data, owned by the user, and opens its page', function () {
    $res = ($this->store)($this->valid);

    $invitation = Invitation::query()->where('user_id', $this->user->id)->firstOrFail();
    $res->assertRedirect(route('dashboard.invitations.show', $invitation));

    expect($invitation->title)->toBe('Raka & Sinta')->and($invitation->template_slug)->toBe('classic-elegant')
        ->and($invitation->status)->toBe(InvitationStatus::Draft)->and($invitation->event_type->value)->toBe('wedding')
        ->and($invitation->slug)->toMatch('/^raka-sinta-[a-z2-9]{4}$/')
        ->and($invitation->people()->count())->toBe(2)->and($invitation->events()->count())->toBeGreaterThan(0)
        ->and($invitation->gifts()->count())->toBeGreaterThan(0);
    $this->get(route('dashboard.invitations.show', $invitation))->assertOk()->assertSee('Undangan dibuat');
});

it('lets the owner title and template win over the sample ones', function () {
    ($this->store)(['title' => 'Dimas dan Alya', 'event_type' => 'engagement'] + $this->valid);

    $invitation = Invitation::query()->firstOrFail();
    expect($invitation->title)->toBe('Dimas dan Alya')->and($invitation->event_type->value)->toBe('engagement')
        ->and($invitation->people()->pluck('role')->map->value->all())->toEqualCanonicalizing(['groom', 'bride']);
});

it('creates an empty draft when asked to start blank', function () {
    ($this->store)(['start' => 'blank'] + $this->valid)->assertRedirect();

    $invitation = Invitation::query()->firstOrFail();
    expect($invitation->people()->count())->toBe(0)->and($invitation->events()->count())->toBe(0)->and($invitation->title)->toBe('Raka & Sinta');
});

it('makes every new invitation a free sample that cannot be published (DEC-01)', function () {
    ($this->store)($this->valid);
    $invitation = Invitation::query()->firstOrFail();

    expect(app(Entitlements::class)->can($invitation, 'publish'))->toBeFalse();
    expect(fn () => app(InvitationLifecycle::class)->publish($invitation))->toThrow(PublishRequiresPlan::class);
    expect($invitation->fresh()->status)->toBe(InvitationStatus::Draft);
});

it('gives two invitations with the same title different slugs', function () {
    ($this->store)($this->valid);
    ($this->store)($this->valid);

    expect(Invitation::query()->pluck('slug')->unique()->count())->toBe(2);
});

it('rejects bad input with Indonesian messages and creates nothing', function (array $override, string $field, string $message) {
    ($this->store)($override + $this->valid)->assertSessionHasErrors([$field => $message]);

    expect(Invitation::query()->count())->toBe(0);
})->with([
    'no title' => [['title' => ''], 'title', 'Judul undangan wajib diisi.'],
    'title too long' => [['title' => str_repeat('a', 121)], 'title', 'Judul undangan terlalu panjang.'],
    'unknown event type' => [['event_type' => 'funeral'], 'event_type', 'Jenis acara tidak tersedia. Pilih dari daftar.'],
    'unknown template' => [['template_slug' => 'ghost'], 'template_slug', 'Template tidak tersedia. Pilih dari daftar.'],
    'path as template' => [['template_slug' => '../../.env'], 'template_slug', 'Template tidak tersedia. Pilih dari daftar.'],
    'no template' => [['template_slug' => ''], 'template_slug', 'Template wajib diisi.'],
    'bad start' => [['start' => 'copy'], 'start', 'Cara memulai tidak tersedia. Pilih dari daftar.'],
]);

it('rejects a template that does not support the event type', function () {
    ($this->store)(['event_type' => 'birthday'] + $this->valid)->assertSessionHasErrors('template_slug');

    expect(Invitation::query()->count())->toBe(0);
});

it('ignores any attempt to create the invitation for someone else', function () {
    $other = User::factory()->create();

    ($this->store)(['user_id' => $other->id, 'status' => 'published', 'slug' => 'sudah-terbit'] + $this->valid);

    $invitation = Invitation::query()->firstOrFail();
    expect($invitation->user_id)->toBe($this->user->id)->and($invitation->status)->toBe(InvitationStatus::Draft)
        ->and($invitation->slug)->not->toBe('sudah-terbit');
});

it('stores a title with markup as plain text and escapes it on every page', function () {
    ($this->store)(['title' => '<script>alert(1)</script> Raka'] + $this->valid);
    $invitation = Invitation::query()->firstOrFail();

    foreach ([route('dashboard'), route('dashboard.invitations.show', $invitation)] as $url) {
        $html = $this->actingAs($this->user)->get($url)->getContent();
        expect($html)->not->toContain('<script>alert(1)')->toContain('&lt;script&gt;alert(1)&lt;/script&gt; Raka');
    }
    expect($invitation->slug)->toMatch('/^[a-z0-9]+(?:-[a-z0-9]+)*$/');
});

it('needs a signed-in, verified user to create anything', function () {
    $this->post(route('dashboard.invitations.store'), $this->valid)->assertRedirect(route('login'));
    $this->actingAs(User::factory()->unverified()->create())->post(route('dashboard.invitations.store'), $this->valid)->assertRedirect(route('verification.notice'));

    expect(Invitation::query()->count())->toBe(0);
});

it('serves template thumbnails to the dashboard and nothing else from the template folders', function () {
    $ok = $this->actingAs($this->user)->get(route('dashboard.template.image', ['template' => 'classic-elegant', 'file' => 'thumb.webp']))->assertOk();
    expect($ok->headers->get('Content-Type'))->toBe('image/webp')->and($ok->headers->get('Cache-Control'))->toContain('max-age=86400');
    $this->actingAs($this->user)->get(route('dashboard.template.image', ['template' => 'classic-elegant', 'file' => 'preview.webp']))->assertOk();

    $base = 'http://'.config('surfaces.app.domain').'/dashboard/templates';
    foreach (['classic-elegant/manifest.json', 'classic-elegant/theme.css', 'classic-elegant/index.blade.php', '../.env', 'ghost/thumb.webp'] as $path) {
        $this->actingAs($this->user)->get("{$base}/{$path}")->assertNotFound();
    }
    auth()->logout();
    $this->get(route('dashboard.template.image', ['template' => 'classic-elegant', 'file' => 'thumb.webp']))->assertRedirect(route('login'));
});
