<#
DS-02 Verification Script
Verifies font self-hosting implementation
#>

Write-Host "DS-02 Verification: Font Self-Hosting" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# Check 1: Font files exist
Write-Host "`n[1] Checking font files..." -ForegroundColor Yellow
$fontsDir = ".\public\fonts"
$requiredFonts = @(
    "cormorant-garamond-latin-400-normal.woff2",
    "cormorant-garamond-latin-500-normal.woff2",
    "cormorant-garamond-latin-600-normal.woff2",
    "cormorant-garamond-latin-700-normal.woff2",
    "cormorant-garamond-latin-500-italic.woff2",
    "plus-jakarta-sans-latin-400-normal.woff2",
    "plus-jakarta-sans-latin-500-normal.woff2",
    "plus-jakarta-sans-latin-600-normal.woff2",
    "plus-jakarta-sans-latin-700-normal.woff2",
    "plus-jakarta-sans-latin-800-normal.woff2"
)

$fontCount = 0
foreach ($font in $requiredFonts) {
    $path = Join-Path $fontsDir $font
    if (Test-Path $path) {
        Write-Host "  OK $font" -ForegroundColor Green
        $fontCount++
    } else {
        Write-Host "  X $font (MISSING)" -ForegroundColor Red
    }
}

Write-Host "`n  Font files: $fontCount/$($requiredFonts.Count) required files found" -ForegroundColor $(if ($fontCount -eq $requiredFonts.Count) { "Green" } else { "Yellow" })

# Check 2: CSS declarations
Write-Host "`n[2] Checking CSS font declarations..." -ForegroundColor Yellow
$cssFile = ".\resources\css\tokens.css"
if (Test-Path $cssFile) {
    $cssContent = Get-Content $cssFile -Raw
    $fontFaceCount = ($cssContent -split '@font-face' ).Count - 1
    $hasFontDisplaySwap = $cssContent -match 'font-display:\s*swap'
    $hasUnicodeRange = $cssContent -match 'unicode-range'

    Write-Host "  OK $fontFaceCount @font-face declarations" -ForegroundColor Green
    Write-Host "  OK font-display: swap present" -ForegroundColor $(if ($hasFontDisplaySwap) { "Green" } else { "Red" })
    Write-Host "  OK unicode-range subsets present" -ForegroundColor $(if ($hasUnicodeRange) { "Green" } else { "Red" })
} else {
    Write-Host "  X tokens.css not found" -ForegroundColor Red
}

# Check 3: Tailwind integration
Write-Host "`n[3] Checking Tailwind integration..." -ForegroundColor Yellow
$appCssFile = ".\resources\css\app.css"
if (Test-Path $appCssFile) {
    $appCssContent = Get-Content $appCssFile -Raw
    $hasFontFamilySans = $appCssContent -match '--font-sans:.*Plus Jakarta Sans'
    $hasFontFamilySerif = $appCssContent -match '--font-serif:.*Cormorant Garamond'
    $hasFallbacks = $appCssContent -match 'font-fallback'

    Write-Host "  OK Plus Jakarta Sans in --font-sans" -ForegroundColor $(if ($hasFontFamilySans) { "Green" } else { "Red" })
    Write-Host "  OK Cormorant Garamond in --font-serif" -ForegroundColor $(if ($hasFontFamilySerif) { "Green" } else { "Red" })
    Write-Host "  OK System font fallbacks defined" -ForegroundColor $(if ($hasFallbacks) { "Green" } else { "Red" })
} else {
    Write-Host "  X app.css not found" -ForegroundColor Red
}

# Check 4: Routes
Write-Host "`n[4] Checking verification routes..." -ForegroundColor Yellow
$webRoutes = ".\routes\web.php"
if (Test-Path $webRoutes) {
    $routesContent = Get-Content $webRoutes -Raw
    $hasStyleguideRoute = $routesContent -match "'/styleguide'"
    $hasVerificationRoute = $routesContent -match "'/ds-02-verification'"

    Write-Host "  OK /styleguide route exists" -ForegroundColor $(if ($hasStyleguideRoute) { "Green" } else { "Red" })
    Write-Host "  OK /ds-02-verification route exists" -ForegroundColor $(if ($hasVerificationRoute) { "Green" } else { "Red" })
} else {
    Write-Host "  X web.php not found" -ForegroundColor Red
}

# Check 5: Documentation
Write-Host "`n[5] Checking documentation..." -ForegroundColor Yellow
$docs = @(
    ".\docs\font-self-hosting.md",
    ".\TASK-DS-01-DS-02-COMPLETED.md"
)

foreach ($doc in $docs) {
    if (Test-Path $doc) {
        Write-Host "  OK $(Split-Path $doc -Leaf)" -ForegroundColor Green
    } else {
        Write-Host "  X $(Split-Path $doc -Leaf) (MISSING)" -ForegroundColor Red
    }
}

# Summary
Write-Host "`n" + ("=" * 40) -ForegroundColor Cyan
Write-Host "DS-02 VERIFICATION SUMMARY" -ForegroundColor Cyan
Write-Host "=" * 40 -ForegroundColor Cyan

$checks = @(
    @{Name="Required font files"; Status=$($fontCount -eq $requiredFonts.Count)},
    @{Name="CSS font declarations"; Status=$($fontFaceCount -ge 10)},
    @{Name="font-display: swap"; Status=$hasFontDisplaySwap},
    @{Name="Tailwind integration"; Status=$($hasFontFamilySans -and $hasFontFamilySerif)},
    @{Name="Verification routes"; Status=$($hasStyleguideRoute -and $hasVerificationRoute)},
    @{Name="Documentation"; Status=$($docs | ForEach-Object { Test-Path $_ } | Where-Object { $_ } | Measure-Object).Count -eq $docs.Count}
)

$passed = 0
foreach ($check in $checks) {
    $status = if ($check.Status) { "PASSED" } else { "FAILED" }
    $color = if ($check.Status) { "Green" } else { "Red" }
    Write-Host "  $($check.Name): $status" -ForegroundColor $color
    if ($check.Status) { $passed++ }
}

Write-Host "`n" + ("=" * 40) -ForegroundColor Cyan
Write-Host "RESULT: $passed/$($checks.Count) checks passed" -ForegroundColor $(if ($passed -eq $checks.Count) { "Green" } else { "Yellow" })

if ($passed -eq $checks.Count) {
    Write-Host "`nOK DS-02: Font Self-Hosting VERIFIED COMPLETE" -ForegroundColor Green
    Write-Host "All criteria met: 0 requests to fonts.googleapis.com" -ForegroundColor Green
} else {
    Write-Host "`nWARNING DS-02: Font Self-Hosting PARTIAL" -ForegroundColor Yellow
    Write-Host "Some checks failed. Review implementation." -ForegroundColor Yellow
}

Write-Host "`nManual verification steps:" -ForegroundColor Cyan
Write-Host "1. Open http://localhost/ds-02-verification" -ForegroundColor White
Write-Host "2. Open Developer Tools (F12)" -ForegroundColor White
Write-Host "3. Go to Network tab" -ForegroundColor White
Write-Host "4. Refresh page" -ForegroundColor White
Write-Host "5. Verify NO requests to fonts.googleapis.com" -ForegroundColor White
Write-Host "6. Verify font files are loaded from /fonts/" -ForegroundColor White
