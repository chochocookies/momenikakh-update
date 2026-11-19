<?php

declare(strict_types=1);

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Tests\Support\DesignSystem;

beforeEach(function () {
    $this->withoutVite();
    $this->owner = User::factory()->create(['name' => 'Rina Contoh']);
    $this->stranger = User::factory()->create();
    $this->seed(PlanSeeder::class);
    $this->pay = fn (Invitation $i) => Subscription::factory()->forInvitation($i)->create(['plan_id' => Plan::query()->where('key', 'paket-template')->value('id'), 'ends_at' => null]);
});

it('sends a visitor who is not signed in to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('dashboard.invitations.create'))->assertRedirect(route('login'));
});

it('sends an unverified user to the verification notice', function () {
    $this->actingAs(User::factory()->unverified()->create())->get(route('dashboard'))->assertRedirect(route('verification.notice'));
});

it('invites a new user to create the first invitation', function () {
    $res = $this->actingAs($this->owner)->get(route('dashboard'))->assertOk();
    $xp = DesignSystem::xpath($res->getContent());

    expect($res->getContent())->toContain('Belum ada undangan')->toContain('Halo, Rina Contoh.')
        ->and($xp->query('//h1')->length)->toBe(1)
        ->and($xp->query('//a[@href="'.route('dashboard.invitations.create').'"]')->length)->toBeGreaterThanOrEqual(2);
});

it('shows only the invitations of the signed-in user', function () {
    Invitation::factory()->for($this->owner)->create(['title' => 'Undangan Rina']);
    Invitation::factory()->for($this->stranger)->create(['title' => 'Undangan Orang Lain']);

    $html = $this->actingAs($this->owner)->get(route('dashboard'))->assertOk()->getContent();

    expect($html)->toContain('Undangan Rina')->not->toContain('Undangan Orang Lain')->not->toContain('Belum ada undangan');
    $other = $this->actingAs($this->stranger)->get(route('dashboard'))->getContent();
    expect($other)->toContain('Undangan Orang Lain')->not->toContain('Undangan Rina');
});

it('labels an invitation as a free sample until it has the Paket Template', function () {
    $sample = Invitation::factory()->for($this->owner)->create(['title' => 'Sample Satu']);
    $paid = Invitation::factory()->for($this->owner)->create(['title' => 'Berbayar Satu']);
    ($this->pay)($paid);

    $xp = DesignSystem::xpath($this->actingAs($this->owner)->get(route('dashboard'))->getContent());

    $card = fn (string $title) => $xp->query('//li[.//a[normalize-space()="'.$title.'"]]')->item(0)->textContent;
    expect($card('Sample Satu'))->toContain('Sample gratis')->not->toContain('Paket Template')
        ->and($card('Berbayar Satu'))->toContain('Paket Template')->not->toContain('Sample gratis')
        ->and($sample->exists)->toBeTrue();
});

it('shows the status, event type and template of each invitation', function () {
    Invitation::factory()->for($this->owner)->wedding()->published()->create(['title' => 'Terbit Satu']);

    $text = $this->actingAs($this->owner)->get(route('dashboard'))->getContent();

    expect($text)->toContain('Terbit')->toContain('Pernikahan')->toContain('Classic Elegant');
});

it('pages the list at 12 invitations and keeps every page private', function () {
    Invitation::factory()->count(14)->for($this->owner)->create();
    Invitation::factory()->count(3)->for($this->stranger)->create();

    $first = DesignSystem::xpath($this->actingAs($this->owner)->get(route('dashboard'))->getContent());
    $second = DesignSystem::xpath($this->actingAs($this->owner)->get(route('dashboard', ['page' => 2]))->getContent());

    expect($first->query('//ul[@aria-label="Daftar undangan"]/li')->length)->toBe(12)
        ->and($second->query('//ul[@aria-label="Daftar undangan"]/li')->length)->toBe(2);
});

it('opens the page of an own invitation and refuses everyone else', function () {
    $invitation = Invitation::factory()->for($this->owner)->create(['title' => 'Halaman Rina']);

    $this->actingAs($this->owner)->get(route('dashboard.invitations.show', $invitation))->assertOk()->assertSee('Halaman Rina');
    $this->actingAs($this->stranger)->get(route('dashboard.invitations.show', $invitation))->assertForbidden();
    auth()->logout();
    $this->get(route('dashboard.invitations.show', $invitation))->assertRedirect(route('login'));
});

it('lets an admin open any invitation', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $invitation = Invitation::factory()->for($this->owner)->create();

    $this->actingAs($admin)->get(route('dashboard.invitations.show', $invitation))->assertOk();
});

it('shows the price of publishing on a free sample, read from the plans table', function () {
    $invitation = Invitation::factory()->for($this->owner)->create();

    $this->actingAs($this->owner)->get(route('dashboard.invitations.show', $invitation))->assertOk()
        ->assertSee('Rp 50.000')->assertSee('sekali bayar per undangan')->assertSee('tanpa masa kedaluwarsa')
        ->assertSee('Sample gratis')->assertDontSee('Salin tautan');

    Plan::query()->where('key', 'paket-template')->update(['price_idr' => 65000]);
    $this->actingAs($this->owner)->get(route('dashboard.invitations.show', $invitation))->assertSee('Rp 65.000')->assertDontSee('Rp 50.000');
});

it('shows the Paket Template state and the public link for a paid, published invitation', function () {
    $invitation = Invitation::factory()->for($this->owner)->published()->create(['slug' => 'rina-dan-adi-abcd']);
    ($this->pay)($invitation);

    $res = $this->actingAs($this->owner)->get(route('dashboard.invitations.show', $invitation))->assertOk();

    $res->assertSee('Paket Template')->assertSee('Salin tautan')->assertSee('/rina-dan-adi-abcd')->assertDontSee('Rp 50.000');
    expect(DesignSystem::xpath($res->getContent())->query('//button[@data-copy]')->length)->toBe(1);
});

it('links to the guest list and the template preview from the invitation page', function () {
    $invitation = Invitation::factory()->for($this->owner)->wedding()->create();

    $html = $this->actingAs($this->owner)->get(route('dashboard.invitations.show', $invitation))->getContent();

    expect($html)->toContain(route('dashboard.guests.index', $invitation))
        ->toContain(route('public.preview', ['template' => 'classic-elegant', 'event' => 'wedding']))
        ->toContain('rel="noopener noreferrer"');
});

it('keeps heading order and labels on every dashboard page', function () {
    $invitation = Invitation::factory()->for($this->owner)->create();

    foreach ([route('dashboard'), route('dashboard.invitations.show', $invitation), route('dashboard.invitations.create'), route('dashboard.invitations.create', ['event' => 'wedding'])] as $url) {
        $xp = DesignSystem::xpath($this->actingAs($this->owner)->get($url)->assertOk()->getContent());
        $previous = 0;
        foreach ($xp->query('//main//h1|//main//h2|//main//h3') as $heading) {
            $level = (int) substr($heading->nodeName, 1);
            expect($previous === 0 || $level <= $previous + 1)->toBeTrue("{$url}: {$heading->nodeName} after h{$previous}");
            $previous = $level;
        }
        expect($xp->query('//h1')->length)->toBe(1, $url);
    }
});
