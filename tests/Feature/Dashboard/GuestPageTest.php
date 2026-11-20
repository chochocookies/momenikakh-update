<?php

declare(strict_types=1);

use App\Domain\Guest\Models\Guest;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\DesignSystem;

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('local');
    $this->owner = User::factory()->create();
    $this->invitation = Invitation::factory()->for($this->owner)->wedding()->complete()->published()->create();
});

it('imports 200 rows, then lists them with a copy button and a WhatsApp link for each', function () {
    $csv = "nama,kelompok,telepon\n".collect(range(1, 200))->map(fn ($i) => "Tamu {$i},Keluarga,0812000".str_pad((string) $i, 4, '0', STR_PAD_LEFT))->implode("\n")."\n";

    $this->actingAs($this->owner)->post(route('dashboard.guests.import.preview', $this->invitation), ['file' => UploadedFile::fake()->createWithContent('tamu.csv', $csv)])->assertOk();
    $id = array_key_first(session('guest_import', []));
    $this->actingAs($this->owner)->post(route('dashboard.guests.import.commit', [$this->invitation, $id]))->assertRedirect();

    expect($this->invitation->guests()->count())->toBe(200);

    $res = $this->actingAs($this->owner)->get(route('dashboard.guests.index', $this->invitation))->assertOk();
    $xp = DesignSystem::xpath($res->getContent());
    expect($xp->query('//button[@data-copy]')->length)->toBe(50)               // one page of 50
        ->and($xp->query('//a[starts-with(@href,"https://wa.me/")][@target="_blank"]')->length)->toBe(50)
        ->and($xp->query('//nav[@aria-label="Halaman"]')->length)->toBe(1);
});

it('puts the guest name in the link as an encoded query value, and the token next to it', function () {
    $guest = Guest::factory()->for($this->invitation)->create(['name' => 'Dr. Budi & Ani/Siti']);

    $res = $this->actingAs($this->owner)->get(route('dashboard.guests.index', $this->invitation))->assertOk();
    $copy = DesignSystem::xpath($res->getContent())->query('//button[@data-copy]')->item(0)->getAttribute('data-copy');

    expect($copy)->toContain('?to=Dr.%20Budi%20%26%20Ani%2FSiti&g='.$guest->token)->toContain($this->invitation->slug);
});

it('lists the guest list first, before the add and import forms', function () {
    Guest::factory()->for($this->invitation)->create();

    $html = $this->actingAs($this->owner)->get(route('dashboard.guests.index', $this->invitation))->getContent();

    expect(strpos($html, 'Tamu dan tautan'))->toBeLessThan(strpos($html, 'Tambah tamu'));
});
