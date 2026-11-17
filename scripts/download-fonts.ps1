<# 
Script untuk mengunduh font self-host untuk Momenikah
Men-download subset Latin dari Google Fonts dengan format WOFF2

Gunakan perintah ini di PowerShell (Windows) untuk menjalankan script:
PS> .\scripts\download-fonts.ps1
#>

# Buat direktori fonts jika belum ada
$fontsDir = ".\public\fonts"
if (-not (Test-Path $fontsDir)) {
    New-Item -ItemType Directory -Path $fontsDir -Force
}

Write-Host "Mengunduh font untuk Momenikah Design System..." -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Cyan

# Daftar font yang perlu diunduh
$fontFiles = @(
    @{Name="cormorant-garamond-latin-400-normal.woff2"; Url="https://fonts.gstatic.com/s/cormorantgaramond/v18/co3bmX5slCNuHLi8bLeY9MK7whWMhyjYrEt7m2Ql.woff2"},
    @{Name="cormorant-garamond-latin-500-normal.woff2"; Url="https://fonts.gstatic.com/s/cormorantgaramond/v18/co3bmX5slCNuHLi8bLeY9MK7whWMhyjYrEt7m2Ql.woff2?weight=500"},
    @{Name="cormorant-garamond-latin-600-normal.woff2"; Url="https://fonts.gstatic.com/s/cormorantgaramond/v18/co3bmX5slCNuHLi8bLeY9MK7whWMhyjYrEt7m2Ql.woff2?weight=600"},
    @{Name="cormorant-garamond-latin-700-normal.woff2"; Url="https://fonts.gstatic.com/s/cormorantgaramond/v18/co3bmX5slCNuHLi8bLeY9MK7whWMhyjYrEt7m2Ql.woff2?weight=700"},
    @{Name="cormorant-garamond-latin-500-italic.woff2"; Url="https://fonts.gstatic.com/s/cormorantgaramond/v18/co3aX5slCNuHLi8bLeY9MK7whWMhyjQlJ3tw.woff2"},
    @{Name="plus-jakarta-sans-latin-400-normal.woff2"; Url="https://fonts.gstatic.com/s/plusjakartasans/v8/LDIoaomQNQcsA88c7O9yZ4KMCoOg4Ko70yygg_hg.woff2"},
    @{Name="plus-jakarta-sans-latin-500-normal.woff2"; Url="https://fonts.gstatic.com/s/plusjakartasans/v8/LDIoaomQNQcsA88c7O9yZ4KMCoOg4Ko70yygg_hg.woff2?weight=500"},
    @{Name="plus-jakarta-sans-latin-600-normal.woff2"; Url="https://fonts.gstatic.com/s/plusjakartasans/v8/LDIoaomQNQcsA88c7O9yZ4KMCoOg4Ko70yygg_hg.woff2?weight=600"},
    @{Name="plus-jakarta-sans-latin-700-normal.woff2"; Url="https://fonts.gstatic.com/s/plusjakartasans/v8/LDIoaomQNQcsA88c7O9yZ4KMCoOg4Ko70yygg_hg.woff2?weight=700"},
    @{Name="plus-jakarta-sans-latin-800-normal.woff2"; Url="https://fonts.gstatic.com/s/plusjakartasans/v8/LDIoaomQNQcsA88c7O9yZ4KMCoOg4Ko70yygg_hg.woff2?weight=800"}
)

# Fungsi untuk mengunduh file
function Download-Font {
    param(
        [string]$fileName,
        [string]$url
    )
    
    $outputPath = Join-Path $fontsDir $fileName
    
    try {
        Write-Host "Mengunduh: $fileName" -ForegroundColor Yellow
        
        # Gunakan Invoke-WebRequest untuk mengunduh
        Invoke-WebRequest -Uri $url -OutFile $outputPath
        
        if (Test-Path $outputPath) {
            $fileSize = (Get-Item $outputPath).Length / 1KB
            Write-Host "  ✓ Berhasil ($([math]::Round($fileSize, 2)) KB)" -ForegroundColor Green
        } else {
            Write-Host "  ✗ Gagal mengunduh" -ForegroundColor Red
        }
    }
    catch {
        Write-Host "  ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Unduh semua font
foreach ($font in $fontFiles) {
    Download-Font -fileName $font.Name -url $font.Url
}

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "Proses download font selesai." -ForegroundColor Green
Write-Host "Font disimpan di: $fontsDir" -ForegroundColor Cyan
Write-Host "Total file: $($fontFiles.Count) font files" -ForegroundColor Cyan

# Verifikasi
Write-Host "`nVerifikasi font files:" -ForegroundColor Cyan
Get-ChildItem $fontsDir -Filter *.woff2 | ForEach-Object {
    $sizeKB = $_.Length / 1KB
    Write-Host "  $($_.Name) ($([math]::Round($sizeKB, 2)) KB)" -ForegroundColor White
}