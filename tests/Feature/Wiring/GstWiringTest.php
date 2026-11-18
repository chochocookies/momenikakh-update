<?php

declare(strict_types=1);

use App\Domain\Guest\Models\Guest;
use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Guest\Models\Rsvp;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\WiringFixtures as Fx;

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('local');
});

function csvUpload(string $contents, string $name = 'tamu.csv'): UploadedFile
{
    return Fx::upload($contents, $name);
}

it('registers every guest route and the public beacon', function () {
    foreach (['index', 'store', 'template', 'import.preview', 'import.commit', 'import.cancel', 'destroy', 'token', 'export.rsvps', 'export.messages'] as $name) {
        expect(Route::has("dashboard.guests.{$name}"))->toBeTrue("missing dashboard.guests.{$name}");
    }
    expect(Route::has('public.guest.opened'))->toBeTrue();
});

it('schedules the media prune and the backup cleanup', function () {
    $commands = collect(app(Schedule::class)->events())->map(fn ($e) => (string) ($e->command ?? $e->description ?? ''))->implode("\n");
    expect($commands)->toContain('media:prune-orphans')->toContain('invitations:expire');
});

it('lets the owner see the guest list with copyable links and a WhatsApp button', function () {
    [$user, $invitation] = Fx::owned();
    Guest::factory()->for($invitation)->create(['name' => 'Budi & Siti <b>', 'phone' => '6281200000001']);

    $res = $this->actingAs($user)->get(route('dashboard.guests.index', $invitation));

    $res->assertOk()->assertSee('Daftar tamu')->assertSee('Budi &amp; Siti &lt;b&gt;', false)->assertSee('https://wa.me/6281200000001?text=', false);
    $res->assertSee('data-copy="'.e('https://undangan.momenikah.test/'.$invitation->slug.'?to=Budi%20%26%20Siti%20%3Cb%3E&g='), false);
});

it('redirects guests to login and forbids other users', function () {
    [, $invitation] = Fx::owned();
    $this->get(route('dashboard.guests.index', $invitation))->assertRedirect();

    $stranger = User::factory()->create();
    $this->actingAs($stranger)->get(route('dashboard.guests.index', $invitation))->assertForbidden();
    $this->actingAs($stranger)->post(route('dashboard.guests.store', $invitation), ['name' => 'X'])->assertForbidden();
    expect(Guest::query()->count())->toBe(0);
});

it('adds a guest by hand, normalises the phone and enforces the plan limit', function () {
    [$user, $invitation] = Fx::owned();
    config(['entitlements.free.limits.max_guests' => 1]);

    $this->actingAs($user)->post(route('dashboard.guests.store', $invitation), ['name' => "  Ani\u{200B}  Lestari ", 'group' => 'Teman', 'phone' => '0812-3456-7890'])
        ->assertRedirect()->assertSessionHas('toast');
    $guest = Guest::query()->firstOrFail();
    expect($guest->name)->toBe('Ani Lestari')->and($guest->phone)->toBe('6281234567890')->and($guest->token)->toMatch('/^[a-z0-9]{12}$/');

    $this->actingAs($user)->post(route('dashboard.guests.store', $invitation), ['name' => 'Kedua'])->assertSessionHasErrors('name');
    expect(Guest::query()->count())->toBe(1);

    $this->actingAs($user)->post(route('dashboard.guests.store', $invitation), ['name' => 'Tanpa Nomor', 'phone' => 'abc'])->assertSessionHasErrors('phone');
});

it('regenerates a token and deletes a guest only through its own invitation', function () {
    [$user, $invitation] = Fx::owned();
    [, $other] = Fx::owned();
    $guest = Guest::factory()->for($invitation)->create(['opened_at' => now()]);
    $foreign = Guest::factory()->for($other)->create();
    $old = $guest->token;

    $this->actingAs($user)->post(route('dashboard.guests.token', [$invitation, $guest]))->assertRedirect();
    $guest->refresh();
    expect($guest->token)->not->toBe($old)->and($guest->opened_at)->toBeNull();

    // A guest of another invitation must not be reachable through my invitation URL (scoped bindings -> 404).
    $this->actingAs($user)->delete(route('dashboard.guests.destroy', [$invitation, $foreign]))->assertNotFound();
    expect(Guest::query()->whereKey($foreign->id)->exists())->toBeTrue();

    $this->actingAs($user)->delete(route('dashboard.guests.destroy', [$invitation, $guest]))->assertRedirect();
    expect(Guest::query()->whereKey($guest->id)->exists())->toBeFalse();
});

it('imports a CSV in two steps: preview writes nothing, confirm re-reads the stored file', function () {
    [$user, $invitation] = Fx::owned();
    $csv = "nama;kelompok;telepon\nBudi Santoso;Keluarga;081200000001\nSiti Rahma;;\nBudi Santoso;Keluarga;081200000001\n;Teman;\nRudi;Teman;12\n";

    $res = $this->actingAs($user)->post(route('dashboard.guests.import.preview', $invitation), ['file' => csvUpload($csv)]);
    $res->assertOk()->assertSee('Pratinjau impor');
    expect(Guest::query()->count())->toBe(0);
    $importId = $res->viewData('importId');
    expect($importId)->toBeString();
    Storage::disk('local')->assertExists("guest-imports/{$importId}.csv");

    $this->actingAs($user)->post(route('dashboard.guests.import.commit', [$invitation, $importId]))
        ->assertRedirect(route('dashboard.guests.index', $invitation));
    expect(Guest::query()->orderBy('name')->pluck('name')->all())->toBe(['Budi Santoso', 'Siti Rahma']);
    Storage::disk('local')->assertMissing("guest-imports/{$importId}.csv");

    // replay: the import id is single use
    $this->actingAs($user)->post(route('dashboard.guests.import.commit', [$invitation, $importId]))->assertStatus(410);
});

it('rejects an import id that belongs to someone else, is expired, or is not a uuid', function () {
    [$user, $invitation] = Fx::owned();
    [$mallory, $malloryInvitation] = Fx::owned();
    $importId = $this->actingAs($user)->post(route('dashboard.guests.import.preview', $invitation), ['file' => csvUpload("nama\nAda\n")])->viewData('importId');

    // Mallory replays the id against her own invitation and against the victim's.
    $this->actingAs($mallory)->post(route('dashboard.guests.import.commit', [$malloryInvitation, $importId]))->assertStatus(410);
    $this->actingAs($mallory)->post(route('dashboard.guests.import.commit', [$invitation, $importId]))->assertForbidden();
    $this->actingAs($user)->post(route('dashboard.guests.import.commit', [$invitation, 'not-a-uuid']))->assertStatus(410);

    $this->travel(config('guests.import.ttl_minutes') + 1)->minutes();
    $this->actingAs($user)->post(route('dashboard.guests.import.commit', [$invitation, $importId]))->assertStatus(410);
    expect(Guest::query()->count())->toBe(0);
});

it('respects the guest limit inside the import and imports all or nothing', function () {
    [$user, $invitation] = Fx::owned();
    config(['entitlements.free.limits.max_guests' => 2]);
    $importId = $this->actingAs($user)->post(route('dashboard.guests.import.preview', $invitation), ['file' => csvUpload("nama\nA\nB\nC\n")])->viewData('importId');

    $this->actingAs($user)->post(route('dashboard.guests.import.commit', [$invitation, $importId]))->assertSessionHasErrors('file');
    expect(Guest::query()->count())->toBe(0);
});

it('reads UTF-16 and Windows-1252 CSV files and rejects binary and formula-looking phone numbers', function () {
    [$user, $invitation] = Fx::owned();
    $utf16 = "\xFF\xFE".mb_convert_encoding("nama,telepon\nÁgústa,081200000009\n", 'UTF-16LE', 'UTF-8');
    $res = $this->actingAs($user)->post(route('dashboard.guests.import.preview', $invitation), ['file' => csvUpload($utf16)]);
    $res->assertOk();
    expect($res->viewData('report')->valid[0]['name'])->toBe('Ágústa');

    $win = mb_convert_encoding("nama,telepon\nJosé,081200000010\n", 'Windows-1252', 'UTF-8');
    $res = $this->actingAs($user)->post(route('dashboard.guests.import.preview', $invitation), ['file' => csvUpload($win)]);
    expect($res->viewData('report')->valid[0]['name'])->toBe('José');

    $res = $this->actingAs($user)->post(route('dashboard.guests.import.preview', $invitation), ['file' => csvUpload("nama,telepon\nA,8.12E+11\n")]);
    expect($res->viewData('report')->errors)->toHaveCount(1);

    $this->actingAs($user)->post(route('dashboard.guests.import.preview', $invitation), ['file' => csvUpload("nama\n\0\0\0\n")])->assertSessionHasErrors('file');
    $this->actingAs($user)->post(route('dashboard.guests.import.preview', $invitation), ['file' => csvUpload('<?php echo 1;', 'evil.php')])->assertSessionHasErrors('file');
});

it('serves the CSV template and cancels a pending import', function () {
    [$user, $invitation] = Fx::owned();
    $res = $this->actingAs($user)->get(route('dashboard.guests.template', $invitation));
    $res->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($res->streamedContent())->toContain('nama,kelompok,telepon');

    $importId = $this->actingAs($user)->post(route('dashboard.guests.import.preview', $invitation), ['file' => csvUpload("nama\nAda\n")])->viewData('importId');
    $this->actingAs($user)->delete(route('dashboard.guests.import.cancel', [$invitation, $importId]))->assertRedirect();
    Storage::disk('local')->assertMissing("guest-imports/{$importId}.csv");
});

it('answers the opened beacon with 204, without a CSRF token, and records only the first open', function () {
    [, $invitation] = Fx::owned();
    $guest = Guest::factory()->for($invitation)->create();
    $url = route('public.guest.opened', $invitation->slug);

    $this->post($url, ['g' => $guest->token])->assertNoContent();
    $first = $guest->fresh()->opened_at;
    expect($first)->not->toBeNull();

    $this->travel(5)->minutes();
    $this->post($url, ['g' => $guest->token])->assertNoContent();
    expect($guest->fresh()->opened_at->equalTo($first))->toBeTrue();

    // unknown token, malformed token, wrong slug: always 204, nothing changes
    $other = Guest::factory()->for($invitation)->create();
    $this->post($url, ['g' => 'zzzzzzzzzzzz'])->assertNoContent();
    $this->post($url, ['g' => "'; DROP TABLE guests;--"])->assertNoContent();
    $this->post(route('public.guest.opened', 'slug-lain'), ['g' => $other->token])->assertNoContent();
    expect($other->fresh()->opened_at)->toBeNull();
});

it('does not record opens for draft invitations', function () {
    [, $invitation] = Fx::owned(InvitationStatus::Draft);
    $guest = Guest::factory()->for($invitation)->create();
    $this->post(route('public.guest.opened', $invitation->slug), ['g' => $guest->token])->assertNoContent();
    expect($guest->fresh()->opened_at)->toBeNull();
});

it('throttles the beacon', function () {
    [, $invitation] = Fx::owned();
    $url = route('public.guest.opened', $invitation->slug);
    foreach (range(1, 60) as $i) {
        $this->post($url, ['g' => 'aaaaaaaaaaaa'])->assertNoContent();
    }
    $this->post($url, ['g' => 'aaaaaaaaaaaa'])->assertStatus(429);
});

it('exports RSVP and messages only with the export_guests ability, only for the owner', function () {
    [$user, $invitation] = Fx::owned();
    Rsvp::factory()->for($invitation)->create(['name' => '=HYPERLINK("http://evil","x")', 'attendance' => 'yes', 'pax' => 2, 'note' => "baris\nbaru, \"kutip\""]);
    GuestMessage::factory()->for($invitation)->create(['name' => '@SUM(A1)', 'message' => '+1+1']);

    $this->actingAs($user)->get(route('dashboard.guests.export.rsvps', $invitation))->assertForbidden(); // free plan

    config(['entitlements.free.features.export_guests' => true]);
    $res = $this->actingAs($user)->get(route('dashboard.guests.export.rsvps', $invitation));
    $res->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($res->headers->get('Cache-Control'))->toContain('no-store')->toContain('private');
    $csv = $res->streamedContent();
    expect($csv)->toStartWith("\xEF\xBB\xBF".'Nama,Kehadiran')->toContain("'=HYPERLINK")->not->toContain(',=HYPERLINK')->toContain('Hadir')->toContain("\r\n");

    $csv = $this->actingAs($user)->get(route('dashboard.guests.export.messages', $invitation))->streamedContent();
    expect($csv)->toContain("'@SUM(A1)")->toContain("'+1+1");

    $csv = $this->actingAs($user)->get(route('dashboard.guests.export.rsvps', [$invitation, 'format' => 'excel-id']))->streamedContent();
    expect($csv)->toContain('Nama;Kehadiran;"Jumlah tamu"');

    $stranger = User::factory()->create();
    $this->actingAs($stranger)->get(route('dashboard.guests.export.rsvps', $invitation))->assertForbidden();
});

it('exports timestamps in the invitation timezone', function () {
    [$user, $invitation] = Fx::owned();
    $invitation->forceFill(['timezone' => 'Asia/Makassar'])->save();
    config(['entitlements.free.features.export_guests' => true]);
    $this->travelTo(CarbonImmutable::parse('2026-12-01 03:00:00', 'UTC'));
    Rsvp::factory()->for($invitation)->create(['name' => 'Tz']);

    $csv = $this->actingAs($user)->get(route('dashboard.guests.export.rsvps', $invitation))->streamedContent();
    expect($csv)->toContain('2026-12-01 11:00');
});

it('exports 1200 rows without error', function () {
    [$user, $invitation] = Fx::owned();
    config(['entitlements.free.features.export_guests' => true]);
    Rsvp::factory()->count(1200)->for($invitation)->create();
    $csv = $this->actingAs($user)->get(route('dashboard.guests.export.rsvps', $invitation))->streamedContent();
    expect(substr_count($csv, "\r\n"))->toBe(1201);
});
