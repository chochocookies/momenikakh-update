<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Billing\Entitlements;
use App\Domain\Billing\SubscriptionResolver;
use App\Domain\Guest\Models\Guest;
use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Guest\Models\Rsvp;
use App\Domain\Guest\Policies\InvitationChildPolicy;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Policies\InvitationPolicy;
use App\Domain\Invitation\Services\InvitationLifecycle;
use App\Domain\Invitation\Services\InvitationWriter;
use App\Domain\Invitation\Services\SlugGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/** Register in bootstrap/providers.php. */
final class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EventTypeRegistry::class, fn () => EventTypeRegistry::fromConfig((array) config('event-types')));
        $this->app->singleton(SubscriptionResolver::class);
        $this->app->singleton(Entitlements::class);
        $this->app->singleton(InvitationLifecycle::class);
        $this->app->singleton(InvitationWriter::class);
        $this->app->singleton(SlugGenerator::class);
    }

    public function boot(): void
    {
        Gate::policy(Invitation::class, InvitationPolicy::class);
        foreach ([Guest::class, Rsvp::class, GuestMessage::class] as $model) {
            Gate::policy($model, InvitationChildPolicy::class);
        }

        // Catch N+1 queries early (not in production).
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
