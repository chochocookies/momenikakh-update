#!/bin/bash

echo "Mengunduh font untuk Momenikah Design System..."
echo "=================================================="

FONTS_DIR="./public/fonts"
mkdir -p "$FONTS_DIR"

# User-Agent Chrome modern agar Google Fonts API mengembalikan format WOFF2
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"

download_google_font() {
    local family="$1"
    local italic="$2"
    local weight="$3"
    local file_name="$4"
    local output_path="$FONTS_DIR/$file_name"

    echo "Mengunduh: $file_name"

    local css_url="https://googleapis.com{family}:ital,wght@${italic},${weight}&subset=latin"
    local woff_url
    woff_url=$(curl -s -A "$UA" "$css_url" | grep -oE 'https://fonts\.gstatic\.com/[^)]+\.woff2' | tail -n 1)

    if [ -n "$woff_url" ] && curl -s -L "$woff_url" -o "$output_path"; then
        local file_size
        file_size=$(wc -c < "$output_path" | tr -d ' ')
        if [ "$file_size" -gt 1000 ]; then
            echo "  ✓ Berhasil ($((file_size / 1024)) KB)"
        else
            echo "  ✗ Gagal: file terlalu kecil / corrupt"
            rm -f "$output_path"
        fi
    else
        echo "  ✗ Gagal menemukan atau mengunduh URL WOFF2"
    fi
}

# Cormorant Garamond (normal 400, 500, 600, 700 & italic 500)
download_google_font "Cormorant+Garamond" "0" "400" "cormorant-garamond-latin-400-normal.woff2"
download_google_font "Cormorant+Garamond" "0" "500" "cormorant-garamond-latin-500-normal.woff2"
download_google_font "Cormorant+Garamond" "0" "600" "cormorant-garamond-latin-600-normal.woff2"
download_google_font "Cormorant+Garamond" "0" "700" "cormorant-garamond-latin-700-normal.woff2"
download_google_font "Cormorant+Garamond" "1" "500" "cormorant-garamond-latin-500-italic.woff2"

# Plus Jakarta Sans (normal 400, 500, 600, 700, 800)
download_google_font "Plus+Jakarta+Sans" "0" "400" "plus-jakarta-sans-latin-400-normal.woff2"
download_google_font "Plus+Jakarta+Sans" "0" "500" "plus-jakarta-sans-latin-500-normal.woff2"
download_google_font "Plus+Jakarta+Sans" "0" "600" "plus-jakarta-sans-latin-600-normal.woff2"
download_google_font "Plus+Jakarta+Sans" "0" "700" "plus-jakarta-sans-latin-700-normal.woff2"
download_google_font "Plus+Jakarta+Sans" "0" "800" "plus-jakarta-sans-latin-800-normal.woff2"

echo "=================================================="
echo "Total file berhasil diunduh: $(ls -1 "$FONTS_DIR"/*.woff2 2>/dev/null | wc -l) / 10"
