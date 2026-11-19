<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Guest\Services\GuestLinkBuilder;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Media\Services\MusicResolver;
use App\Domain\Templates\InvitationViewModelBuilder;
use App\Domain\Templates\TemplateLinter;
use App\Domain\Templates\TemplateRegistry;
use App\Domain\Templates\TemplateRenderer;
use App\Domain\Templates\TemplateScaffolder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/** Register in bootstrap/providers.php. Wires the template engine (TPL-01..03). */
final class TemplateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TemplateRegistry::class, fn () => new TemplateRegistry(
            (string) config('templates.path'),
            (string) config('templates.cache'),
        ));

        $this->app->singleton(InvitationViewModelBuilder::class, fn ($app) => new InvitationViewModelBuilder(
            $app->make(EventTypeRegistry::class),
            $app->make(TemplateRegistry::class),
            $app->make(GuestLinkBuilder::class),
            $app->make(MusicResolver::class),
            (string) config('templates.default'),
        ));

        $this->app->singleton(TemplateRenderer::class);
        $this->app->singleton(TemplateScaffolder::class);
        $this->app->singleton(TemplateLinter::class, fn ($app) => new TemplateLinter(
            $app->make(Filesystem::class),
            $app->make(TemplateRegistry::class),
            resource_path('css/invitation-core.css'),
        ));
    }

    public function boot(): void
    {
        // templates::classic-elegant.index  =>  resources/templates/classic-elegant/index.blade.php
        View::addNamespace('templates', (string) config('templates.path'));

        /*
         * @invitationSection('cover') renders the template's own sections/cover.blade.php when it exists and
         * falls back to templates::_shared.sections.cover. It is @includeFirst with the lookup written once.
         * Needs $template (TemplateManifest) in scope; TemplateRenderer always provides it.
         */
        Blade::directive('invitationSection', fn (string $expression): string => '<?php echo $__env->first('
            ."['templates::'.\$template->slug.'.sections.'.({$expression}), 'templates::_shared.sections.'.({$expression})], "
            .'\Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>');
    }
}
