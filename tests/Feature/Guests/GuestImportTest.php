<?php

declare(strict_types=1);

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Guest\Models\Guest;
use App\Domain\Guest\Services\GuestCsvParser;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->owner = User::factory()->create();
    $this->invitation = Invitation::factory()->for($this->owner)->create();
    $this->csv = fn (string $body, string $name = 'tamu.csv') => UploadedFile::fake()->createWithContent($name, $body);
    $this->preview = fn (string $body, ?User $as = null) => $this->actingAs($as ?? $this->owner)
        ->post(route('dashboard.guests.import.preview', $this->invitation), ['file' => ($this->csv)($body)]);
    $this->importId = function () {
        $keys = array_keys(session('guest_import', []));

        return $keys[0] ?? null;
    };
});

it('serves a CSV template with fictional rows', function () {
    $res = $this->actingAs($this->owner)->get(route('dashboard.guests.template', $this->invitation))->assertOk();
    expect($res->headers->get('Content-Type'))->toContain('text/csv');
    $body = $res->streamedContent();
    expect($body)->toContain('nama,kelompok,telepon')->toContain('081200000001');
});

it('previews without writing anything', function () {
    ($this->preview)("nama,kelompok,telepon\nBudi,Keluarga,081234567890\nSiti,Teman,\n")
        ->assertOk()->assertSee('Siap diimpor');
    expect($this->invitation->guests()->count())->toBe(0);
    expect(Storage::disk('local')->allFiles('guest-imports'))->toHaveCount(1);
});

it('imports the previewed rows on confirmation and cleans up', function () {
    ($this->preview)("nama,kelompok,telepon\nBudi,Keluarga,081234567890\nSiti,Teman,\n");
    $id = ($this->importId)();
    expect($id)->not->toBeNull();

    $this->actingAs($this->owner)->post(route('dashboard.guests.import.commit', [$this->invitation, $id]))
        ->assertRedirect(route('dashboard.guests.index', $this->invitation));

    expect($this->invitation->guests()->count())->toBe(2);
    expect($this->invitation->guests()->where('name', 'Budi')->first()->phone)->toBe('6281234567890');
    expect(Storage::disk('local')->allFiles('guest-imports'))->toBeEmpty();
    expect(session('guest_import.'.$id))->toBeNull();
});

it('does not let a confirmation be replayed', function () {
    ($this->preview)("nama\nBudi\n");
    $id = ($this->importId)();
    $this->actingAs($this->owner)->post(route('dashboard.guests.import.commit', [$this->invitation, $id]))->assertRedirect();
    $this->actingAs($this->owner)->post(route('dashboard.guests.import.commit', [$this->invitation, $id]))->assertStatus(410);
    expect($this->invitation->guests()->count())->toBe(1);
});

it('reports invalid rows but still imports the valid ones', function () {
    $res = ($this->preview)("nama,telepon\nBudi,081234567890\n,0812\nSiti,abc\n")->assertOk();
    $res->assertSee('Nama wajib diisi.')->assertSee('Nomor telepon tidak valid');
    $this->actingAs($this->owner)->post(route('dashboard.guests.import.commit', [$this->invitation, ($this->importId)()]));
    expect($this->invitation->guests()->pluck('name')->all())->toBe(['Budi']);
});

it('skips rows that already exist for this invitation', function () {
    Guest::factory()->for($this->invitation)->create(['name' => 'Budi', 'phone' => '6281234567890']);
    $res = ($this->preview)("nama,telepon\nbudi,081234567890\nSiti,\n")->assertOk();
    $res->assertSee('Duplikat dilewati');
    $this->actingAs($this->owner)->post(route('dashboard.guests.import.commit', [$this->invitation, ($this->importId)()]));
    expect($this->invitation->guests()->count())->toBe(2);
});

it('rejects a file with no name column and stores nothing', function () {
    ($this->preview)("kelompok,telepon\nA,0812\n")->assertSessionHasErrors('file');
    expect(Storage::disk('local')->allFiles('guest-imports'))->toBeEmpty();
});

it('rejects files over the row limit', function () {
    config(['guests.import.max_rows' => 3]);
    app()->forgetInstance(GuestCsvParser::class);
    ($this->preview)("nama\nA\nB\nC\nD\n")->assertSessionHasErrors('file');
    expect($this->invitation->guests()->count())->toBe(0);
});

it('rejects uploads that are not CSV', function () {
    $this->actingAs($this->owner)->post(route('dashboard.guests.import.preview', $this->invitation), [
        'file' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'),
    ])->assertSessionHasErrors('file');
    $this->actingAs($this->owner)->post(route('dashboard.guests.import.preview', $this->invitation), [
        'file' => UploadedFile::fake()->create('foto.csv', 10, 'image/png'),
    ])->assertSessionHasErrors('file');
    $this->actingAs($this->owner)->post(route('dashboard.guests.import.preview', $this->invitation), [])->assertSessionHasErrors('file');
});

it('imports nothing when the plan guest limit would be exceeded', function () {
    $plan = Plan::factory()->withLimits(['max_guests' => 2])->create();
    Subscription::factory()->for($this->owner)->create(['plan_id' => $plan->id, 'invitation_id' => $this->invitation->id]);
    ($this->preview)("nama\nA\nB\nC\n");
    $this->actingAs($this->owner)->post(route('dashboard.guests.import.commit', [$this->invitation, ($this->importId)()]))
        ->assertSessionHasErrors('file');
    expect($this->invitation->guests()->count())->toBe(0);   // all or nothing
});

it('keeps spreadsheet formulas as plain text in the database', function () {
    ($this->preview)("nama\n=HYPERLINK(\"http://evil\")\n");
    $this->actingAs($this->owner)->post(route('dashboard.guests.import.commit', [$this->invitation, ($this->importId)()]));
    expect($this->invitation->guests()->first()->name)->toBe('=HYPERLINK("http://evil")');
});

it('does not let another user confirm someone else\'s preview', function () {
    ($this->preview)("nama\nBudi\n");
    $id = ($this->importId)();
    $stranger = User::factory()->create();
    $this->actingAs($stranger)->post(route('dashboard.guests.import.commit', [$this->invitation, $id]))->assertForbidden();
    expect($this->invitation->guests()->count())->toBe(0);
});

it('rejects a forged or non-uuid import id', function () {
    $this->actingAs($this->owner)->post(route('dashboard.guests.import.commit', [$this->invitation, 'not-a-uuid']))->assertStatus(410);
    $this->actingAs($this->owner)->post(route('dashboard.guests.import.commit', [$this->invitation, (string) Str::uuid()]))->assertStatus(410);
});

it('expires an old preview', function () {
    ($this->preview)("nama\nBudi\n");
    $id = ($this->importId)();
    $this->travel(config('guests.import.ttl_minutes') + 1)->minutes();
    $this->actingAs($this->owner)->post(route('dashboard.guests.import.commit', [$this->invitation, $id]))->assertStatus(410);
});

it('can cancel a preview and removes the stored file', function () {
    ($this->preview)("nama\nBudi\n");
    $id = ($this->importId)();
    $this->actingAs($this->owner)->delete(route('dashboard.guests.import.cancel', [$this->invitation, $id]))->assertRedirect();
    expect(Storage::disk('local')->allFiles('guest-imports'))->toBeEmpty();
});
