<?php

declare(strict_types=1);

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Guest\Models\Guest;
use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Guest\Models\Rsvp;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

require_once __DIR__.'/../Media/MediaTestCase.php';

beforeEach(function () {
    $this->withoutVite();
    useFakeMediaDisk();
    $this->owner = User::factory()->create(['name' => 'Rina Contoh', 'email' => 'rina@example.test', 'password' => 'rahasia-123']);
    $this->other = User::factory()->create();
    $this->invitation = Invitation::factory()->for($this->owner)->wedding()->complete()->published()->create();
    Guest::factory()->for($this->invitation)->create(['name' => 'Tamu Rina']);
    Rsvp::factory()->create(['invitation_id' => $this->invitation->id, 'name' => 'Pengirim RSVP']);
    GuestMessage::factory()->create(['invitation_id' => $this->invitation->id, 'message' => 'Ucapan untuk Rina']);
    $this->theirs = Invitation::factory()->for($this->other)->wedding()->complete()->create(['title' => 'Milik Orang Lain']);
    $this->erase = fn (array $data = []) => $this->actingAs($this->owner)->delete(route('dashboard.account.destroy'), $data + ['password' => 'rahasia-123', 'confirm' => 'HAPUS']);
});

it('shows the account page to a signed-in owner only', function () {
    $this->get(route('dashboard.account'))->assertRedirect(route('login'));
    $this->actingAs($this->owner)->get(route('dashboard.account'))->assertOk()->assertSee('Rina Contoh')->assertSee('rina@example.test')->assertSee('Hapus akun');
});

it('exports the owner\'s data, and only theirs, without any secret', function () {
    $res = $this->actingAs($this->owner)->get(route('dashboard.account.export'))->assertOk();
    $json = $res->streamedContent();
    $data = json_decode($json, true);

    expect($res->headers->get('Content-Disposition'))->toContain('data-momenikah-')->toContain('.json')
        ->and($data['account']['email'])->toBe('rina@example.test')
        ->and($data['invitations'])->toHaveCount(1)
        ->and($data['invitations'][0]['guests'][0]['name'])->toBe('Tamu Rina')
        ->and($data['invitations'][0]['rsvps'][0]['name'])->toBe('Pengirim RSVP')
        ->and($data['invitations'][0]['wishes'][0]['message'])->toBe('Ucapan untuk Rina')
        ->and($json)->not->toContain('Milik Orang Lain')->not->toContain('password')->not->toContain('remember_token');
});

it('keeps visitors out of the export', function () {
    $this->get(route('dashboard.account.export'))->assertRedirect(route('login'));
});

it('deletes the account with the invitations, guests, answers, wishes and files', function () {
    $this->invitation->addMedia(fakeUpload('ok-800x600.jpg'))->toMediaCollection('cover');
    expect($this->invitation->getMedia('cover'))->toHaveCount(1);
    $path = $this->invitation->getFirstMedia('cover')->getPathRelativeToRoot();
    Storage::disk('media')->assertExists($path);
    $id = $this->invitation->id;

    ($this->erase)()->assertRedirect();

    expect(User::query()->find($this->owner->id))->toBeNull()
        ->and(Invitation::query()->withTrashed()->where('user_id', $this->owner->id)->count())->toBe(0)
        ->and(Guest::query()->count())->toBe(0)->and(Rsvp::query()->count())->toBe(0)->and(GuestMessage::query()->count())->toBe(0);
    Storage::disk('media')->assertMissing($path);
    $this->assertGuest();
    expect(Invitation::query()->whereKey($this->theirs->id)->exists())->toBeTrue()->and(User::query()->find($this->other->id))->not->toBeNull();
    $row = AuditLog::query()->where('action', 'invitation.deleted')->latest('id')->firstOrFail();
    expect(json_encode($row->toArray()))->not->toContain('Rina')->not->toContain('rina@example.test')->and($row->invitation_id)->toBeNull();
});

it('does not delete with a wrong password or without the confirmation word', function (array $bad, string $field) {
    ($this->erase)($bad)->assertSessionHasErrors($field);

    expect(User::query()->find($this->owner->id))->not->toBeNull()->and(Invitation::query()->whereKey($this->invitation->id)->exists())->toBeTrue();
})->with([
    'wrong password' => [['password' => 'salah-sekali'], 'password'],
    'no word' => [['confirm' => ''], 'confirm'],
    'wrong word' => [['confirm' => 'hapus saja'], 'confirm'],
]);

it('answers a wrong password in Indonesian', function () {
    ($this->erase)(['password' => 'salah-sekali'])->assertSessionHasErrors(['password' => 'Kata sandi tidak cocok.']);
});

it('keeps visitors out of the deletion', function () {
    $this->delete(route('dashboard.account.destroy'), ['password' => 'rahasia-123', 'confirm' => 'HAPUS'])->assertRedirect(route('login'));

    expect(User::query()->find($this->owner->id))->not->toBeNull();
});
