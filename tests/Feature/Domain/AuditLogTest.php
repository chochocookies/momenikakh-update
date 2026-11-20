<?php

declare(strict_types=1);

use App\Domain\Audit\AuditLogger;
use App\Domain\Audit\Enums\AuditAction;
use App\Domain\Audit\Models\AuditLog;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Guest\Models\Guest;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Services\InvalidTransition;
use App\Domain\Invitation\Services\InvitationLifecycle;
use App\Domain\Invitation\Services\PublishRequiresPlan;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->user = User::factory()->create();
    $this->invitation = Invitation::factory()->for($this->user)->create(['template_slug' => 'classic-elegant']);
    $this->pay = fn (Invitation $i) => Subscription::factory()->forInvitation($i)->create(['plan_id' => Plan::query()->where('key', 'paket-template')->value('id'), 'ends_at' => null]);
    $this->lifecycle = app(InvitationLifecycle::class);
});

it('records who published an invitation, what and when', function () {
    $this->travelTo(now()->setDate(2027, 1, 10)->setTime(12, 0));
    ($this->pay)($this->invitation);

    $this->lifecycle->publish($this->invitation, by: $this->user);

    $log = AuditLog::query()->sole();
    expect($log->action)->toBe(AuditAction::InvitationPublished)->and($log->user_id)->toBe($this->user->id)
        ->and($log->subject_type)->toBe('invitation')->and($log->subject_id)->toBe($this->invitation->id)
        ->and($log->invitation_id)->toBe($this->invitation->id)->and($log->context)->toBe(['template' => 'classic-elegant'])
        ->and($log->created_at->equalTo(now()))->toBeTrue();
});

it('records each state change with the old and the new state', function () {
    ($this->pay)($this->invitation);
    $this->lifecycle->publish($this->invitation, by: $this->user);
    $this->lifecycle->unpublish($this->invitation, $this->user);
    $this->lifecycle->archive($this->invitation, $this->user);
    $this->lifecycle->restore($this->invitation, $this->user);

    $rows = AuditLog::query()->orderBy('id')->get();

    expect($rows->pluck('action')->map->value->all())->toBe(['invitation.published', 'invitation.unpublished', 'invitation.archived', 'invitation.restored'])
        ->and($rows[1]->context)->toBe(['from' => 'published', 'to' => 'draft'])
        ->and($rows[2]->context)->toBe(['from' => 'draft', 'to' => 'archived'])
        ->and($rows->pluck('user_id')->unique()->all())->toBe([$this->user->id]);
});

it('records nothing for a refused transition or a refused publish', function () {
    expect(fn () => $this->lifecycle->publish($this->invitation, by: $this->user))->toThrow(PublishRequiresPlan::class);
    expect(fn () => $this->lifecycle->expire($this->invitation, $this->user))->toThrow(InvalidTransition::class);

    expect(AuditLog::query()->count())->toBe(0)->and($this->invitation->fresh()->status)->toBe(InvitationStatus::Draft);
});

it('records the scheduler as one system row for a whole expiry sweep, and nothing when nothing is due', function () {
    expect($this->lifecycle->expireDue())->toBe(0)->and(AuditLog::query()->count())->toBe(0);

    Invitation::factory()->count(3)->published()->create(['expires_at' => now()->subHour()]);
    expect($this->lifecycle->expireDue())->toBe(3);

    $log = AuditLog::query()->sole();
    expect($log->action)->toBe(AuditAction::InvitationExpired)->and($log->user_id)->toBeNull()->and($log->context)->toBe(['count' => 3, 'reason' => 'due']);
});

it('keeps only whitelisted, short, scalar context values: no names, emails, phones or IPs', function () {
    $log = app(AuditLogger::class)->record(AuditAction::PaymentPaid, $this->user, null, $this->invitation->id, [
        'order_number' => 'MNK-001', 'amount_idr' => 50000, 'plan' => 'paket-template',
        'email' => 'rina@example.test', 'phone' => '08123456789', 'name' => 'Rina Contoh', 'ip' => '203.0.113.9',
        'reason' => str_repeat('x', 300), 'count' => ['nested' => 'array'],
    ]);

    expect($log->context)->toBe(['order_number' => 'MNK-001', 'amount_idr' => 50000, 'plan' => 'paket-template', 'reason' => str_repeat('x', 80)]);
    $stored = json_encode(DB::table('audit_logs')->first());
    expect($stored)->not->toContain('rina@example.test')->not->toContain('08123456789')->not->toContain('Rina Contoh')->not->toContain('203.0.113.9');
});

it('stores no context at all when nothing is allowed', function () {
    $log = app(AuditLogger::class)->record(AuditAction::MessageHidden, $this->user, null, null, ['message' => 'secret text']);

    expect($log->context)->toBeNull();
});

it('records a deleted guest by id only, never by name or phone', function () {
    $guest = Guest::factory()->for($this->invitation)->create(['name' => 'Budi Rahasia', 'phone' => '6281234567890']);

    $this->actingAs($this->user)->delete(route('dashboard.guests.destroy', [$this->invitation, $guest]))->assertRedirect();

    $log = AuditLog::query()->sole();
    expect($log->action)->toBe(AuditAction::GuestDeleted)->and($log->user_id)->toBe($this->user->id)
        ->and($log->subject_type)->toBe('guest')->and($log->subject_id)->toBe($guest->id)->and($log->invitation_id)->toBe($this->invitation->id);
    expect(json_encode(DB::table('audit_logs')->first()))->not->toContain('Budi Rahasia')->not->toContain('6281234567890');
});

it('does not log a guest deletion that was refused', function () {
    $guest = Guest::factory()->for($this->invitation)->create();

    $this->actingAs(User::factory()->create())->delete(route('dashboard.guests.destroy', [$this->invitation, $guest]))->assertForbidden();

    expect(AuditLog::query()->count())->toBe(0);
});

it('records a guest import with the number of guests only', function () {
    $csv = UploadedFile::fake()->createWithContent('tamu.csv', "nama,kelompok,telepon\nAni,Keluarga,081200000001\nBudi,Teman,081200000002\n");
    $res = $this->actingAs($this->user)->post(route('dashboard.guests.import.preview', $this->invitation), ['file' => $csv])->assertOk();
    preg_match('#import/([0-9a-f-]{36})#', $res->getContent(), $m);

    $this->post(route('dashboard.guests.import.commit', [$this->invitation, $m[1]]))->assertRedirect();

    $log = AuditLog::query()->sole();
    expect($log->action)->toBe(AuditAction::GuestsImported)->and($log->context)->toBe(['count' => 2]);
    expect(json_encode(DB::table('audit_logs')->first()))->not->toContain('Ani')->not->toContain('081200000001');
});

it('outlives the invitation and the user it points to', function () {
    ($this->pay)($this->invitation);
    $this->lifecycle->publish($this->invitation, by: $this->user);

    $this->invitation->forceDelete();
    $this->user->delete();

    $log = AuditLog::query()->sole();
    expect($log->user_id)->toBeNull()->and($log->invitation_id)->not->toBeNull()->and($log->action)->toBe(AuditAction::InvitationPublished);
});

it('has an index for each way the log is read', function () {
    $indexes = collect(Schema::getIndexes('audit_logs'))->pluck('columns');

    expect($indexes->contains(['subject_type', 'subject_id']))->toBeTrue()
        ->and($indexes->contains(['invitation_id', 'created_at']))->toBeTrue()
        ->and($indexes->contains(['user_id', 'created_at']))->toBeTrue()
        ->and($indexes->contains(['action']))->toBeTrue();
});

it('fixes the fillable columns and has no updated_at', function () {
    expect((new AuditLog)->getFillable())->toBe(['user_id', 'action', 'subject_type', 'subject_id', 'invitation_id', 'context'])
        ->and(AuditLog::UPDATED_AT)->toBeNull();
});
