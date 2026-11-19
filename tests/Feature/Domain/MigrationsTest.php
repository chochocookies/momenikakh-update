<?php

declare(strict_types=1);

use App\Domain\Invitation\Models\Invitation;
use Illuminate\Support\Facades\Schema;

it('creates every domain table with its key columns', function () {
    $expected = [
        'users' => ['role', 'phone'],
        'invitations' => ['user_id', 'event_type', 'template_slug', 'slug', 'title', 'status', 'locale', 'timezone', 'content', 'settings', 'published_at', 'expires_at', 'deleted_at'],
        'invitation_people' => ['invitation_id', 'role', 'name', 'nickname', 'parents_text', 'bio', 'birth_date', 'instagram', 'sort'],
        'invitation_events' => ['invitation_id', 'kind', 'label', 'starts_at', 'ends_at', 'venue_name', 'address', 'maps_url', 'latitude', 'longitude', 'dress_code', 'notes', 'sort'],
        'invitation_story_items' => ['invitation_id', 'title', 'body', 'happened_on', 'sort'],
        'invitation_gifts' => ['invitation_id', 'type', 'provider', 'holder_name', 'number', 'address_text', 'is_active', 'sort'],
        'guests' => ['invitation_id', 'name', 'group', 'phone', 'token', 'opened_at'],
        'rsvps' => ['invitation_id', 'guest_id', 'name', 'attendance', 'pax', 'note', 'ip_hash'],
        'guest_messages' => ['invitation_id', 'guest_id', 'name', 'message', 'is_visible'],
        'invitation_daily_stats' => ['invitation_id', 'date', 'views', 'unique_visitors', 'opens'],
        'plans' => ['key', 'name', 'tier', 'price_idr', 'duration_days', 'features', 'limits', 'is_active', 'sort'],
        'subscriptions' => ['user_id', 'plan_id', 'invitation_id', 'status', 'starts_at', 'ends_at'],
        'payments' => ['user_id', 'plan_id', 'subscription_id', 'order_number', 'provider', 'provider_reference', 'amount_idr', 'status', 'payload', 'paid_at'],
    ];
    foreach ($expected as $table => $columns) {
        expect(Schema::hasTable($table))->toBeTrue("missing table {$table}");
        expect(Schema::hasColumns($table, $columns))->toBeTrue("missing columns on {$table}");
    }
});

it('enforces uniqueness on slug, guest token, plan key, order number and daily stats', function () {
    $unique = [
        'invitations' => 'slug', 'guests' => 'token', 'plans' => 'key', 'payments' => 'order_number',
    ];
    foreach ($unique as $table => $column) {
        $indexes = collect(Schema::getIndexes($table))->filter(fn ($i) => $i['unique'] && $i['columns'] === [$column]);
        expect($indexes)->not->toBeEmpty("{$table}.{$column} must be unique");
    }
    $stats = collect(Schema::getIndexes('invitation_daily_stats'))->filter(fn ($i) => $i['unique'] && $i['columns'] === ['invitation_id', 'date']);
    expect($stats)->not->toBeEmpty();
});

it('cascades child rows when an invitation is force deleted', function () {
    $invitation = Invitation::factory()->complete()->create();
    $id = $invitation->id;
    expect(DB::table('invitation_people')->where('invitation_id', $id)->count())->toBeGreaterThan(0);
    $invitation->forceDelete();
    foreach (['invitation_people', 'invitation_events', 'invitation_story_items', 'invitation_gifts'] as $t) {
        expect(DB::table($t)->where('invitation_id', $id)->count())->toBe(0, $t);
    }
});

it('rolls back and migrates again cleanly', function () {
    // Roll back every migration from the invitations table onwards (later migrations such as music/media included).
    $steps = count(array_filter(
        array_map('basename', glob(database_path('migrations/*.php')) ?: []),
        fn (string $file): bool => $file >= '2026_11_16_000110',
    ));
    $this->artisan('migrate:rollback', ['--step' => $steps, '--force' => true])->assertSuccessful();
    foreach (['payments', 'subscriptions', 'plans', 'guests', 'invitations'] as $t) {
        expect(Schema::hasTable($t))->toBeFalse("{$t} should be dropped");
    }
    $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    expect(Schema::hasTable('invitations'))->toBeTrue();
});
