<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing;

use App\Domain\Billing\PlanCatalog;
use App\Domain\Templates\Labels;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;

/** The landing page at `/`. The six looks below are sample phones (fictional data), see resources/css/landing.css. */
final class HomeController extends Controller
{
    /** @var list<array{key:string,name:string,desc:string}> */
    public const SHOWCASE = [
        ['key' => 'classic', 'name' => 'Classic Elegant', 'desc' => 'Formal dengan bingkai ganda dan huruf serif yang tenang.'],
        ['key' => 'beach', 'name' => 'Beach Sunset', 'desc' => 'Langit senja dan ombak yang bergerak pelan.'],
        ['key' => 'floral', 'name' => 'Floral Garden', 'desc' => 'Kelopak bunga jatuh lembut di atas warna blush.'],
        ['key' => 'lux', 'name' => 'Luxury Gold', 'desc' => 'Latar gelap dengan kilau emas yang menyapu layar.'],
        ['key' => 'min', 'name' => 'Modern Minimal', 'desc' => 'Garis tipis dan tipografi tebal yang bersih.'],
        ['key' => 'rus', 'name' => 'Rustic Vintage', 'desc' => 'Kertas kraft hangat dengan cap tanggal.'],
    ];

    public function __invoke(PlanCatalog $plans): View
    {
        // The sample wedding is always 90 days ahead, so the countdown in the phone never shows zeros.
        $day = CarbonImmutable::now('Asia/Jakarta')->addDays(90)->setTime(9, 0);

        return view('marketing.home', [
            'showcase' => self::SHOWCASE,
            'dateLong' => Labels::date($day),
            'dateShort' => $day->format('d · m · Y'),
            'countdownTarget' => $day->toIso8601String(),
            'plans' => $plans->summary(),
        ]);
    }
}
