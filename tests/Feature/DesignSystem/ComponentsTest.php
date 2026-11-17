<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\Support\DesignSystem;

beforeEach(function () {
    $this->withoutVite();
    View::share('errors', new ViewErrorBag());
});

function renderWithErrors(string $blade, array $messages): string
{
    $bag = (new ViewErrorBag())->put('default', new MessageBag($messages));
    View::share('errors', $bag);

    return Blade::render($blade);
}

it('renders a native button by default and a link when href is given', function () {
    $button = Blade::render('<x-ui.button>Simpan</x-ui.button>');
    expect($button)->toContain('<button')->toContain('type="button"')->not->toContain('<a ');

    $link = Blade::render('<x-ui.button href="/daftar">Daftar</x-ui.button>');
    expect($link)->toContain('<a ')->toContain('href="/daftar"')->not->toContain('<button');
});

it('lets a button be a submit button', function () {
    expect(Blade::render('<x-ui.button type="submit">Kirim</x-ui.button>'))->toContain('type="submit"');
});

it('exposes a busy and disabled state while loading', function () {
    $html = Blade::render('<x-ui.button :loading="true">Menyimpan</x-ui.button>');
    expect($html)->toContain('aria-busy="true"')->toContain('disabled');
});

it('gives buttons a touch target of at least 44px', function () {
    $html = Blade::render('<x-ui.button>Simpan</x-ui.button>');
    expect(preg_match('/\b(min-h|h)-(11|12|14)\b|min-h-\[(4[4-9]|[5-9]\d)px\]/', $html))->toBe(1);
});

it('ties label, input, hint and error together for assistive tech', function () {
    $html = renderWithErrors(
        '<x-ui.field name="email" label="Email" hint="Tidak dibagikan." required><x-ui.input type="email" /></x-ui.field>',
        ['email' => ['Format email tidak valid.']],
    );
    $xp = DesignSystem::xpath($html);

    $input = $xp->query('//input[@name="email"]')->item(0);
    expect($input)->not->toBeNull();
    $id = $input->getAttribute('id');
    expect($id)->not->toBe('');
    expect($xp->query('//label[@for="'.$id.'"]')->length)->toBe(1);
    expect($input->getAttribute('aria-invalid'))->toBe('true');
    expect($input->hasAttribute('required'))->toBeTrue();

    $described = array_filter(explode(' ', $input->getAttribute('aria-describedby')));
    expect($described)->toHaveCount(2);
    foreach ($described as $ref) {
        expect($xp->query('//*[@id="'.$ref.'"]')->length)->toBe(1, "aria-describedby points to missing id {$ref}");
    }
    expect($xp->query('//*[@role="alert"]')->length)->toBe(1);
    expect($html)->toContain('wajib');
});

it('omits aria-describedby when there is no hint and no error', function () {
    $xp = DesignSystem::xpath(Blade::render('<x-ui.field name="nama" label="Nama"><x-ui.input /></x-ui.field>'));
    $input = $xp->query('//input[@name="nama"]')->item(0);
    expect($input->hasAttribute('aria-describedby'))->toBeFalse();
    expect($input->hasAttribute('aria-invalid'))->toBeFalse();
});

it('builds valid ids from dotted and bracketed names', function () {
    $xp = DesignSystem::xpath(Blade::render('<x-ui.field name="form.people[0].name" label="Nama"><x-ui.input /></x-ui.field>'));
    $id = $xp->query('//input')->item(0)->getAttribute('id');
    expect($id)->toMatch('/^[A-Za-z][A-Za-z0-9_-]*$/');
});

it('passes livewire and html attributes through to the control', function () {
    $html = Blade::render('<x-ui.field name="nama" label="Nama"><x-ui.input wire:model="nama" autocomplete="name" /></x-ui.field>');
    expect($html)->toContain('wire:model="nama"')->toContain('autocomplete="name"');
});

it('renders a modal as a native dialog with an accessible name and close control', function () {
    $html = Blade::render('<x-ui.modal name="hapus" title="Hapus undangan">Yakin?</x-ui.modal>');
    $xp = DesignSystem::xpath($html);
    $dialog = $xp->query('//dialog')->item(0);
    expect($dialog)->not->toBeNull();
    $labelled = $dialog->getAttribute('aria-labelledby');
    expect($xp->query('//*[@id="'.$labelled.'"]')->length)->toBe(1);
    expect($xp->query('//button[@aria-label and @data-modal-close]')->length)->toBeGreaterThanOrEqual(1);
});

it('does not rely on inline event handlers or inline scripts in components', function () {
    $offenders = [];
    foreach (DesignSystem::views() as $f) {
        if (str_starts_with($f['rel'], 'resources/views/components/') &&
            preg_match('/\son[a-z]+="|javascript:|<script(?![^>]*(ld\+json|nonce))/i', $f['content'])) {
            $offenders[] = $f['rel'];
        }
    }
    expect($offenders)->toBeEmpty();
});

it('has an empty-state, card and badge component', function () {
    foreach (['empty-state', 'card', 'badge', 'select', 'textarea', 'tabs'] as $name) {
        expect(is_file(resource_path("views/components/ui/{$name}.blade.php")))->toBeTrue("missing x-ui.{$name}");
    }
});
