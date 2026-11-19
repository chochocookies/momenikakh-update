<?php

declare(strict_types=1);

use App\Domain\Billing\Models\Plan;
use Carbon\CarbonImmutable;
use Database\Seeders\PlanSeeder;
use Tests\Support\DesignSystem;
use Tests\Support\Surfaces;

beforeEach(function () {
    $this->withoutVite();
    $this->home = fn () => $this->get(Surfaces::url('marketing', '/'));
});

it('serves the landing page at / with one h1 and the promise of the brand', function () {
    $res = ($this->home)()->assertOk();
    $xp = DesignSystem::xpath($res->getContent());

    expect($xp->query('//h1')->length)->toBe(1)
        ->and($xp->query('//h1')->item(0)->textContent)->toBe('Satu link untuk semua momen nikahmu.')
        ->and($xp->query('//body/@data-surface')->item(0)->nodeValue)->toBe('marketing')
        ->and($xp->query('//html/@lang')->item(0)->nodeValue)->toBe('id');
    $res->assertHeader('X-Surface', 'marketing');
});

it('is indexable and has a unique title, a description of 120 to 155 characters and a canonical', function () {
    $html = ($this->home)()->getContent();
    $xp = DesignSystem::xpath($html);

    $title = $xp->query('//title')->item(0)->textContent;
    $description = $xp->query('//meta[@name="description"]/@content')->item(0)->nodeValue;
    expect(mb_strlen($title))->toBeLessThanOrEqual(60)->and($title)->toContain('Momenikah')
        ->and(mb_strlen($description))->toBeGreaterThanOrEqual(120)->toBeLessThanOrEqual(155)
        ->and($xp->query('//meta[@name="robots"]')->length)->toBe(0)
        ->and($xp->query('//link[@rel="canonical"]')->length)->toBe(1);
});

it('sends every "Buat undangan" button to the registration on the app host', function () {
    $xp = DesignSystem::xpath(($this->home)()->getContent());
    $register = route('register');

    $links = [];
    foreach ($xp->query('//a[contains(normalize-space(.), "Buat undangan")]/@href') as $href) {
        $links[] = $href->nodeValue;
    }

    expect($register)->toStartWith('http://'.config('surfaces.app.domain'))->and(count($links))->toBeGreaterThanOrEqual(3)
        ->and(array_unique($links))->toBe([$register]);
});

it('shows the six template looks as a phone, a chip and a gallery card each', function () {
    $xp = DesignSystem::xpath(($this->home)()->getContent());

    expect($xp->query('//*[@id="hero"]//*[contains(@class,"ph")]')->length)->toBe(6)
        ->and($xp->query('//*[@id="hero"]//*[contains(@class,"ph") and not(@hidden)]')->length)->toBe(1)
        ->and($xp->query('//button[contains(@class,"lp-chip")]')->length)->toBe(6)
        ->and($xp->query('//button[contains(@class,"lp-chip") and @aria-pressed="true"]')->length)->toBe(1)
        ->and($xp->query('//*[@id="gaya"]//li[contains(@class,"lp-gc")]')->length)->toBe(6)
        ->and($xp->query('//*[@id="gaya"]//*[contains(@class,"ph")][@aria-hidden="true"]')->length)->toBe(6);
});

it('has readable content without JavaScript: text in the HTML, no inline script or style, no external font', function () {
    $html = ($this->home)()->getContent();
    $xp = DesignSystem::xpath($html);

    expect($html)->toContain('Tanpa unduh aplikasi.')->toContain('Pilih template')->toContain('Hitung mundur')
        ->not->toContain('fonts.googleapis.com')->not->toContain('fonts.gstatic.com')
        ->and($xp->query('//script[not(@src)]')->length)->toBe(0)
        ->and($xp->query('//*[@style]')->length)->toBe(0);
});

it('keeps headings in order and labels every section', function () {
    $xp = DesignSystem::xpath(($this->home)()->getContent());

    $previous = 0;
    foreach ($xp->query('//main//h1|//main//h2|//main//h3') as $heading) {
        $level = (int) substr($heading->nodeName, 1);
        expect($previous === 0 || $level <= $previous + 1)->toBeTrue("{$heading->nodeName} after h{$previous}");
        $previous = $level;
    }
    foreach ($xp->query('//main//section[@aria-labelledby]') as $section) {
        expect($xp->query('//*[@id="'.$section->getAttribute('aria-labelledby').'"]')->length)->toBe(1);
    }
});

it('counts down to a sample wedding that is always in the future and never shows a real person', function () {
    $html = ($this->home)()->getContent();
    $xp = DesignSystem::xpath($html);

    $target = $xp->query('//*[@data-countdown-target]/@data-countdown-target')->item(0)->nodeValue;
    expect(CarbonImmutable::parse($target)->isFuture())->toBeTrue()->and($target)->toMatch('/\+07:00$/')
        ->and($html)->toContain('Raka &amp; Sinta')->toContain('Semua data di halaman ini hanya contoh.');
});

it('reads the prices from the plans table (DEC-01): free sample and Rp 50.000', function () {
    $this->seed(PlanSeeder::class);

    ($this->home)()->assertSee('Rp 0')->assertSee('Rp 50.000')->assertSee('Gratis')->assertSee('Paket Template')
        ->assertSee('Tanpa masa kedaluwarsa')->assertSee('Belum bisa diterbitkan');
});

it('follows a changed price and never invents one', function () {
    $this->seed(PlanSeeder::class);
    Plan::query()->where('key', 'paket-template')->update(['price_idr' => 75000]);

    ($this->home)()->assertSee('Rp 75.000')->assertDontSee('Rp 50.000');
});

it('still renders with the config prices when the plans are not seeded', function () {
    ($this->home)()->assertOk()->assertSee('Rp 50.000');
});

it('makes no false claims: no rating, review, user count or testimonial', function () {
    $text = mb_strtolower(strip_tags(($this->home)()->getContent()));

    foreach (['bintang', 'ulasan', 'testimoni', 'pengguna puas', 'ribu pasangan', 'terpercaya', 'terbaik', '★', 'aggregaterating'] as $claim) {
        expect($text)->not->toContain($claim);
    }
});

it('keeps /styleguide next to it outside production only', function () {
    $this->get(Surfaces::url('marketing', 'styleguide'))->assertOk();
});
