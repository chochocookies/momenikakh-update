#!/usr/bin/env python3
"""Momenikah brand kit generator: logo SVG/PNG, favicon set, PWA icons, OG image.
Usage: pip install fonttools cairosvg pillow && python3 .claude/brand/build-brand.py
Fonts (SIL OFL) are downloaded once into .claude/brand/.fonts and outlined into paths,
so no font file is needed at runtime. Edit the TOKENS below, then re-run."""
import io, json, os, urllib.request
from pathlib import Path
from fontTools.ttLib import TTFont
from fontTools.varLib import instancer
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
import cairosvg
from PIL import Image

ROOT = Path(__file__).parent
FONTS = ROOT / ".fonts"
TOKENS = dict(ink="#2B1B22", rose="#BE185D", rose_light="#F472B6", rose_soft="#FCE7F3",
              ivory="#FFF8F3", muted="#6B5A62", gold="#B8893B")
FONT_URLS = {
 "CormorantGaramond-VF.ttf": "https://raw.githubusercontent.com/google/fonts/main/ofl/cormorantgaramond/CormorantGaramond%5Bwght%5D.ttf",
 "PlusJakartaSans-VF.ttf": "https://raw.githubusercontent.com/google/fonts/main/ofl/plusjakartasans/PlusJakartaSans%5Bwght%5D.ttf"}
T = TOKENS
HEART = ("M32 25C32 25 22 19 22 13C22 9.5 25 7.5 27.5 7.5C29.5 7.5 31.2 8.8 32 10.5"
         "C32.8 8.8 34.5 7.5 36.5 7.5C39 7.5 42 9.5 42 13C42 19 32 25 32 25Z")

def load(name, wght):
    FONTS.mkdir(exist_ok=True)
    p = FONTS / name
    if not p.exists():
        urllib.request.urlretrieve(FONT_URLS[name], p)
    f = TTFont(p)
    instancer.instantiateVariableFont(f, {"wght": wght}, inplace=True)
    return f

SERIF = load("CormorantGaramond-VF.ttf", 600)
SANS = load("PlusJakartaSans-VF.ttf", 600)
SANS_REG = load("PlusJakartaSans-VF.ttf", 500)

def text(font, s, size, x, y, fill, spacing=0.0, fills=None):
    """Return (svg, width). `fills` = {char_index: color} to recolour single glyphs."""
    gs, cmap, upm = font.getGlyphSet(), font.getBestCmap(), font["head"].unitsPerEm
    k, cur, out = size / upm, x, []
    for i, ch in enumerate(s):
        g = gs[cmap[ord(ch)]]
        pen = SVGPathPen(gs)
        g.draw(TransformPen(pen, (k, 0, 0, -k, cur, y)))
        d = pen.getCommands()
        if d:
            out.append(f'<path d="{d}" fill="{(fills or {}).get(i, fill)}"/>')
        cur += g.width * k + spacing
    return "".join(out), cur - x - spacing

def icon(a, b, h, uid, weave=True, sw=4.5):
    s = (f'<circle cx="24" cy="40" r="14" fill="none" stroke="{a}" stroke-width="{sw}"/>'
         f'<circle cx="40" cy="40" r="14" fill="none" stroke="{b}" stroke-width="{sw}"/>')
    if weave:
        s += (f'<clipPath id="w{uid}"><rect x="27" y="46" width="10" height="12"/></clipPath>'
              f'<circle cx="24" cy="40" r="14" fill="none" stroke="{a}" stroke-width="{sw}" clip-path="url(#w{uid})"/>')
    return s + f'<path d="{HEART}" fill="{h}"/>'

def svg(w, h, body, title="Momenikah"):
    return (f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {w:g} {h:g}" width="{w:g}" height="{h:g}" '
            f'role="img" aria-label="{title}"><title>{title}</title>{body}</svg>')

def put(path, data):
    path.parent.mkdir(parents=True, exist_ok=True)
    (path.write_text if isinstance(data, str) else path.write_bytes)(data)

def png(svg_str, path, w=None, h=None):
    put(path, cairosvg.svg2png(bytestring=svg_str.encode(), output_width=w, output_height=h))

LOGO, FAV, SOC = ROOT / "logo", ROOT / "favicon", ROOT / "social"

# ---------- icons (square 64 box, content centered) ----------
put(LOGO / "momenikah-icon.svg", svg(64, 64, icon(T["ink"], T["rose"], T["rose"], "c")))
put(LOGO / "momenikah-icon-reverse.svg", svg(64, 64, icon(T["ivory"], T["rose_light"], T["rose_light"], "r")))
put(LOGO / "momenikah-icon-mono.svg", svg(64, 64, f'<g color="currentColor">{icon("currentColor","currentColor","currentColor","m",False)}</g>'))

# ---------- wordmark: "Mome[n]ikah", the shared "n" is the brand device ----------
WM_FILL = {4: T["rose"]}
def wordmark(size, x, y, base, accent):
    return text(SERIF, "Momenikah", size, x, y, base, 0.4, {4: accent})
body, w = wordmark(56, 0, 44, T["ink"], T["rose"])
put(LOGO / "momenikah-wordmark.svg", svg(w + 4, 64, body.replace("<path", "<path", 0)))
def horizontal(base, accent, ic):
    wm, w = wordmark(56, 80, 50, base, accent)
    return svg(80 + w + 8, 72, f'<g transform="translate(-2 -1) scale(1.15)">{ic}</g>{wm}'), 80 + w + 8
for name, base, acc, ic in [
    ("momenikah-logo-horizontal", T["ink"], T["rose"], icon(T["ink"], T["rose"], T["rose"], "h1")),
    ("momenikah-logo-horizontal-reverse", T["ivory"], T["rose_light"], icon(T["ivory"], T["rose_light"], T["rose_light"], "h2"))]:
    s, wd = horizontal(base, acc, ic)
    put(LOGO / f"{name}.svg", s)
png(horizontal(T["ink"], T["rose"], icon(T["ink"], T["rose"], T["rose"], "h3"))[0], LOGO / "momenikah-logo-horizontal.png", w=1200)
png(horizontal(T["ivory"], T["rose_light"], icon(T["ivory"], T["rose_light"], T["rose_light"], "h4"))[0], LOGO / "momenikah-logo-horizontal-reverse.png", w=1200)

# stacked lockup with tagline
tag = "Satu link untuk semua momen nikahmu."
wm, ww = wordmark(72, 0, 0, T["ink"], T["rose"])
tg, tw = text(SANS_REG, tag, 17, 0, 0, T["muted"], 0.2)
W = max(ww, tw) + 80
stk = (f'<g transform="translate({(W-96)/2:.1f} 20) scale(1.5)">{icon(T["ink"], T["rose"], T["rose"], "s")}</g>'
       f'<g transform="translate({(W-ww)/2:.1f} 190)">{wm}</g><g transform="translate({(W-tw)/2:.1f} 228)">{tg}</g>')
put(LOGO / "momenikah-logo-stacked.svg", svg(W, 252, stk))
png(svg(W, 252, stk), LOGO / "momenikah-logo-stacked.png", w=1200)

# ---------- favicon + app icons ----------
def tile(size, scale, rx, bg=T["rose"], uid="f"):
    inner = icon("#FFD3E4", "#FFFFFF", "#FFFFFF", uid, True, 4.8)
    t = 32 - 32 * scale
    r = f'<rect width="64" height="64" rx="{rx}" fill="{bg}"/>' if rx is not None else f'<rect width="64" height="64" fill="{bg}"/>'
    return svg(64, 64, f'{r}<g transform="translate({t:.2f} {t:.2f}) scale({scale})">{inner}</g>')
put(FAV / "favicon.svg", tile(64, 0.86, 14, uid="fa"))
for n, px, sc, rx in [("favicon-16x16", 16, .92, 12), ("favicon-32x32", 32, .9, 13),
                      ("apple-touch-icon", 180, .74, None), ("icon-192", 192, .86, 14),
                      ("icon-512", 512, .86, 14), ("icon-maskable-512", 512, .64, None)]:
    png(tile(px, sc, rx, uid=n[:6]), FAV / f"{n}.png", w=px, h=px)
big = Image.open(io.BytesIO(cairosvg.svg2png(bytestring=tile(256, .9, 13, uid="ico").encode(), output_width=256, output_height=256)))
big.save(FAV / "favicon.ico", format="ICO", sizes=[(16, 16), (32, 32), (48, 48)])
put(FAV / "site.webmanifest", json.dumps({
    "name": "Momenikah", "short_name": "Momenikah", "description": "Undangan pernikahan digital. Satu link untuk semua momen nikahmu.",
    "lang": "id", "start_url": "/", "scope": "/", "display": "standalone",
    "theme_color": T["rose"], "background_color": T["ivory"],
    "icons": [{"src": "/icon-192.png", "sizes": "192x192", "type": "image/png", "purpose": "any"},
              {"src": "/icon-512.png", "sizes": "512x512", "type": "image/png", "purpose": "any"},
              {"src": "/icon-maskable-512.png", "sizes": "512x512", "type": "image/png", "purpose": "maskable"}]},
    indent=2, ensure_ascii=False))

# ---------- Open Graph / Twitter card 1200x630 ----------
lines = ["Satu link untuk", "semua momen", "nikahmu."]
og = f'<rect width="1200" height="630" fill="{T["ivory"]}"/>'
og += f'<g transform="translate(70 62) scale(1.0)">{horizontal(T["ink"], T["rose"], icon(T["ink"], T["rose"], T["rose"], "o1"))[0].split("</title>")[1].rsplit("</svg>",1)[0]}</g>'
for i, ln in enumerate(lines):
    col = T["rose"] if i == 2 else T["ink"]
    o, _ = text(SERIF, ln, 84, 70, 270 + i * 92, col, 0)
    og += o
dom, _ = text(SANS, "momenikah.online", 26, 70, 566, T["rose"], 0.3)
og += dom
og += (f'<g transform="translate(760 90)"><rect width="360" height="450" rx="44" fill="{T["rose"]}"/>'
       f'<g transform="translate(-20 45) scale(6.4)">{icon("#FFD3E4", "#FFFFFF", "#FFFFFF", "o2")}</g></g>')
og_svg = svg(1200, 630, og, "Momenikah, undangan pernikahan digital")
put(SOC / "og-default.svg", og_svg)
png(og_svg, SOC / "og-default.png", w=1200, h=630)
print("brand kit built:", sorted(str(p.relative_to(ROOT)) for p in ROOT.rglob("*") if p.is_file() and ".fonts" not in str(p)))
