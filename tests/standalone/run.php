<?php

declare(strict_types=1);

/*
 * Standalone checks for the framework-free classes of GST-01..03 and MED-02/03/05.
 * Run without Laravel:   php tests/standalone/run.php
 * Needs: php-gd (with WebP), fileinfo, mbstring.  Exit code 1 if any check fails.
 */

$root = dirname(__DIR__, 2);
spl_autoload_register(function (string $class) use ($root): void {
    $file = $root.'/app/'.str_replace(['App\\', '\\'], ['', '/'], $class).'.php';
    if (is_file($file)) {
        require $file;
    }
});
if (! function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $default;
    }
}

use App\Domain\Guest\Export\CsvExporter;
use App\Domain\Guest\Services\GuestCsvParser;
use App\Domain\Guest\Services\GuestLinkBuilder;
use App\Domain\Guest\Services\PhoneNormalizer;
use App\Domain\Guest\Services\ShareMessageBuilder;
use App\Domain\Media\Exceptions\SanitizeException;
use App\Domain\Media\Services\AudioInspector;
use App\Domain\Media\Services\ExifOrientation;
use App\Domain\Media\Services\ImageInspector;
use App\Domain\Media\Services\ImageSanitizer;

$fail = 0;
$pass = 0;
function check(string $name, bool $ok, string $detail = ''): void
{
    global $fail, $pass;
    if ($ok) {
        $pass++;
        echo "  ok   $name\n";
    } else {
        $fail++;
        echo "  FAIL $name".($detail !== '' ? "  -> $detail" : '')."\n";
    }
}
function section(string $t): void
{
    echo "\n== $t\n";
}
$fx = fn (string $f) => $root.'/tests/fixtures/'.$f;
$tmp = sys_get_temp_dir().'/mn-standalone-'.bin2hex(random_bytes(4));
mkdir($tmp);
$csv = function (string $content) use ($tmp): string {
    $p = $tmp.'/'.bin2hex(random_bytes(4)).'.csv';
    file_put_contents($p, $content);

    return $p;
};

// ---------------------------------------------------------------- GST-01
section('PhoneNormalizer');
$ph = new PhoneNormalizer;
$cases = [
    '081234567890' => '6281234567890', '0812-3456-7890' => '6281234567890', '+62 812 3456 7890' => '6281234567890',
    '6281234567890' => '6281234567890', '81234567890' => '6281234567890', '(0812) 3456 7890' => '6281234567890',
    '+6208123456789' => '628123456789', '0062812345678' => '62812345678', '+1 415 555 0132' => '14155550132',
    '' => null, '   ' => null, 'abc' => null, '123' => null, '8.12E+11' => null, '0000' => null, '+0123456789' => null,
    '021 555 1234' => '62215551234',
];
foreach ($cases as $in => $want) {
    $got = $ph->normalize((string) $in);
    check("phone '$in'", $got === $want, 'got '.var_export($got, true));
}
check('phone null', $ph->normalize(null) === null);

section('GuestCsvParser');
$parser = new GuestCsvParser($ph, maxRows: 5, maxBytes: 4096);
$r = $parser->parse($csv("nama,kelompok,telepon\nBudi Santoso,Keluarga,081234567890\nSiti,Teman,\n"));
check('valid rows imported', count($r->valid) === 2 && $r->errors === [] && $r->canCommit());
check('phone normalised in report', $r->valid[0]['phone'] === '6281234567890' && $r->valid[1]['phone'] === null);
check('line numbers reported', $r->valid[0]['line'] === 2 && $r->valid[1]['line'] === 3);

$r = $parser->parse($csv("Nama;Grup;No HP\nAni;Kantor;0812 111 2222\n"));
check('semicolon delimiter + alias headers', count($r->valid) === 1 && $r->valid[0]['group'] === 'Kantor' && $r->valid[0]['phone'] === '628121112222');

$r = $parser->parse($csv("\xEF\xBB\xBFnama\n\"Ni Luh Ayu, SE\"\n"));
check('BOM stripped and quoted comma kept', $r->valid[0]['name'] === 'Ni Luh Ayu, SE');

$r = $parser->parse($csv("nama\n".mb_convert_encoding('Jos'."\u{e9}", 'Windows-1252', 'UTF-8')."\n"));
check('Windows-1252 file converted', ($r->valid[0]['name'] ?? '') === "Jos\u{e9}");

$r = $parser->parse($csv("nama,telepon\n,0812\nBudi,abc\n".str_repeat('x', 150).",0812345678901\nOk,081234567890\n"));
check('invalid rows rejected with field + line', count($r->errors) >= 3 && count($r->valid) === 1);
$fields = array_map(fn ($e) => $e['field'].'@'.$e['line'], $r->errors);
check('error details', in_array('name@2', $fields, true) && in_array('phone@3', $fields, true) && in_array('name@4', $fields, true), implode(',', $fields));
check('phone error explains scientific notation fix', str_contains($r->errors[array_search('phone@3', $fields, true)]['message'], 'teks'));

$r = $parser->parse($csv("nama,telepon\nBudi,081234567890\nbudi,0812-3456-7890\nBudi,081111111111\n"));
check('duplicates (same name+phone, case-insens.) skipped', count($r->valid) === 2 && $r->duplicates === [3]);

$r = $parser->parse($csv("nama\nA\nB\nC\nD\nE\nF\n"));
check('row limit is fatal and imports nothing', $r->fatal !== null && ! $r->canCommit() && $r->valid === [], (string) $r->fatal);
$r = $parser->parse($csv("nama\nA\nB\nC\nD\nE\n"));
check('exactly at limit is fine', count($r->valid) === 5);

$r = $parser->parse($csv("kelompok,telepon\nA,0812\n"));
check('missing name column is fatal', $r->fatal !== null && str_contains($r->fatal, 'nama'));
check('empty file fatal', $parser->parse($csv(''))->fatal !== null);
check('header only fatal', $parser->parse($csv("nama\n"))->fatal !== null);
check('blank lines ignored', count($parser->parse($csv("nama\n\n\nBudi\n\n"))->valid) === 1);
check('binary file rejected', $parser->parse($csv("nama\n\0\0\0"))->fatal !== null);
check('oversize file rejected', $parser->parse($csv("nama\n".str_repeat("Budi\n", 2000)))->fatal !== null);
check('missing file', $parser->parse($tmp.'/nope.csv')->fatal !== null);
$r = $parser->parse($csv("nama\n=HYPERLINK(\"http://evil\")\n"));
check('formula text is imported as plain text (neutralised on export, not altered here)', ($r->valid[0]['name'] ?? '') === '=HYPERLINK("http://evil")');
$r = $parser->parse($csv("nama\nBudi\x00\u{202E}Santoso\u{200B}\n"));
check('control + bidi characters stripped from names', ! preg_match('/[\x00\x{202E}\x{200B}]/u', $r->valid[0]['name'] ?? '?'), $r->valid[0]['name'] ?? '');

$r = $parser->parse($csv("nama\n\u{0645}\u{06CC}\u{200C}\u{062E}\u{0648}\u{0627}\u{0647}\u{0645}\n\u{1F468}\u{200D}\u{1F469}\u{200D}\u{1F467}\n"));
check('ZWNJ (Persian) and ZWJ (emoji) are kept in names', ($r->valid[0]['name'] ?? '') === "\u{0645}\u{06CC}\u{200C}\u{062E}\u{0648}\u{0627}\u{0647}\u{0645}" && str_contains($r->valid[1]['name'] ?? '', "\u{200D}"));

// ---------------------------------------------------------------- GST-02
section('GuestLinkBuilder + ShareMessageBuilder');
$lb = new GuestLinkBuilder('https://undangan.momenikah.test/');
$names = ['Budi Santoso', 'Ni Luh Ayu & Keluarga', "O'Brien", 'Budi/Ani', '<script>alert(1)</script>', 'A+B', '100% hadir', 'Zoë', 'Dr. Éric #1', 'Tamu "VIP"', "Ahmad\u{202E}evil"];
foreach ($names as $n) {
    $url = $lb->forGuest('raka-sinta-x7k2', $n, 'abc123def456');
    $parts = parse_url($url);
    parse_str($parts['query'], $q);
    $expected = $lb->cleanName($n);
    check("link round-trips name: $n", ($q['to'] ?? null) === $expected && ($q['g'] ?? null) === 'abc123def456' && $parts['path'] === '/raka-sinta-x7k2' && $parts['host'] === 'undangan.momenikah.test', $url);
}
check('no token => no g param', ! str_contains($lb->forGuest('x-abcd', 'Budi'), 'g='));
check('plain link', $lb->plain('raka-sinta-x7k2') === 'https://undangan.momenikah.test/raka-sinta-x7k2');
check('slug is encoded', str_contains($lb->plain('a b'), 'a%20b'));
check('name truncated to 60', mb_strlen(urldecode(explode('to=', $lb->forGuest('x-abcd', str_repeat('a', 100)))[1])) === 60);
check('wa.me with phone', str_starts_with($lb->whatsapp('6281234567890', 'Halo & selamat'), 'https://wa.me/6281234567890?text=Halo%20%26%20selamat'));
check('wa.me phone sanitised', str_starts_with($lb->whatsapp('+62 812-3456', 'x'), 'https://wa.me/628123456?text='));
check('wa.me without phone', str_starts_with($lb->whatsapp(null, 'x'), 'https://wa.me/?text=') && str_starts_with($lb->whatsapp('', 'x'), 'https://wa.me/?text='));
$msg = new ShareMessageBuilder('Halo {nama}, undangan {judul}: {tautan} {rahasia}', 200);
$built = $msg->build(null, 'Budi', 'Raka & Sinta', 'https://u.test/x?to=Budi&g=abc');
check('message placeholders filled, unknown removed', $built === 'Halo Budi, undangan Raka & Sinta: https://u.test/x?to=Budi&g=abc', $built);
$custom = $msg->build('Salam {nama}! {tautan}', 'Siti', 'T', 'https://u.test/y');
check('custom template', $custom === 'Salam Siti! https://u.test/y');
$long = $msg->build(str_repeat('a', 400).' {tautan}', 'Budi', 'T', 'https://u.test/z');
check('overlong template falls back without cutting the link', str_contains($long, 'https://u.test/z') && mb_strlen($long) <= 200, $long);

// ---------------------------------------------------------------- GST-03
section('CsvExporter');
$ex = new CsvExporter(',');
$attacks = ['=HYPERLINK("http://evil.test","x")', '+1+1', '-2+3', '@SUM(1,1)', "\t=1+1", ' =1+1', "\r=1+1", "=cmd|' /C calc'!A0", "\n=1+1", '＝1+1'];
$out = $ex->toString(['Nama'], array_map(fn ($a) => [$a], $attacks));
check('BOM present', str_starts_with($out, "\xEF\xBB\xBF"));
$h = fopen('php://memory', 'r+');
fwrite($h, substr($out, 3));
rewind($h);
$read = [];
while (($row = fgetcsv($h, 0, ',', '"', '')) !== false) {
    $read[] = $row;
} fclose($h);
foreach (array_slice($read, 1) as $i => $row) {
    $cell = $row[0];
    $dangerous = preg_match('/^[\t\r\n\0 ]*[=+\-@]/', $attacks[$i]) === 1 || $attacks[$i][0] === "\t" || $attacks[$i][0] === "\r";
    $isSafe = $dangerous ? str_starts_with($cell, "'") : $cell === $attacks[$i];
    check('attack neutralised: '.json_encode($attacks[$i]), $isSafe, json_encode($cell));
}
$safe = $ex->toString(['Nama', 'Ucapan', 'Jumlah'], [['Budi, "Si" Besar', "Selamat\nberbahagia", 3], ['Zoë 日本', 'ok', 0], [null, true, 2.5]]);
$h = fopen('php://memory', 'r+');
fwrite($h, substr($safe, 3));
rewind($h);
$rows = [];
while (($row = fgetcsv($h, 0, ',', '"', '')) !== false) {
    $rows[] = $row;
}
check('quotes and newlines survive a round trip', $rows[1] === ['Budi, "Si" Besar', "Selamat\nberbahagia", '3'], json_encode($rows[1]));
check('unicode preserved', $rows[2][0] === 'Zoë 日本');
check('null -> empty, bool -> ya, numbers untouched', $rows[3] === ['', 'ya', '2.5'], json_encode($rows[3]));
check('numbers are not prefixed', CsvExporter::neutralize(-5) === -5 && CsvExporter::neutralize(0) === 0);
check('plain text untouched', CsvExporter::neutralize('Budi = baik') === 'Budi = baik');
check('headers neutralised too', str_contains((new CsvExporter)->toString(['=bad'], []), "'=bad"));
check('semicolon delimiter for Indonesian Excel', str_contains((new CsvExporter(';'))->toString(['A', 'B'], [['1', '2']]), 'A;B'));
check('CRLF line endings', str_contains($safe, "\r\n"));
$count = 0;
$hh = fopen('php://memory', 'r+');
$count = $ex->write($hh, ['A'], (function () {
    for ($i = 0; $i < 1000; $i++) {
        yield [(string) $i];
    }
})());
check('streams generators and reports row count', $count === 1000);

// ---------------------------------------------------------------- MED-03 / MED-02
$limits = require $root.'/config/media-limits.php';
$insp = new ImageInspector($limits['image'], $limits['profiles']);
$san = new ImageSanitizer($limits['image'], $limits['profiles']);

section('ImageInspector (MED-03)');
$res = $insp->inspect($fx('ok-800x600.jpg'), 'gallery');
check('good JPEG accepted', $res->ok && $res->mime === 'image/jpeg' && $res->width === 800 && $res->height === 600, json_encode($res->errors));
check('PHP file named .jpg rejected', ! $insp->inspect($fx('fake-php.jpg'), 'gallery')->ok && $insp->inspect($fx('fake-php.jpg'), 'gallery')->has('bad_mime'));
$svg = $insp->inspect($fx('svg-as.png'), 'gallery');
check('SVG disguised as PNG rejected with clear message', ! $svg->ok && str_contains($svg->firstMessage(), 'SVG'), (string) $svg->firstMessage());
$bomb = $insp->inspect($fx('bomb.png'), 'gallery');
check('decompression bomb rejected from the header (no decode)', ! $bomb->ok && $bomb->has('too_many_pixels'), json_encode($bomb->errors));
check('too small for cover', $insp->inspect($fx('tiny.jpg'), 'cover')->has('too_small'));
check('800x600 too small for cover (needs 1200x630)', $insp->inspect($fx('ok-800x600.jpg'), 'cover')->has('too_small'));
check('truncated JPEG: header ok, caught at sanitize', true);
check('missing file', $insp->inspect($tmp.'/none.jpg', 'gallery')->has('unreadable'));
$empty = $tmp.'/empty.jpg';
file_put_contents($empty, '');
check('empty file', $insp->inspect($empty, 'gallery')->has('empty'));
$tight = new ImageInspector(['max_bytes' => 1000] + $limits['image'], $limits['profiles']);
check('over size limit', $tight->inspect($fx('big.jpg'), 'gallery')->has('too_large'));
$gif = $tmp.'/a.gif';
file_put_contents($gif, base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'));
check('GIF rejected', $insp->inspect($gif, 'gallery')->has('bad_mime'));
check('transparent PNG accepted for qr', $insp->inspect($fx('transparent.png'), 'qr')->ok);

section('ImageSanitizer: orientation (MED-02)');
$expect = ['TL' => [255, 0, 0], 'TR' => [0, 255, 0], 'BL' => [0, 0, 255], 'BR' => [255, 255, 0]];
$near = fn (array $a, array $b) => abs($a[0] - $b[0]) < 60 && abs($a[1] - $b[1]) < 60 && abs($a[2] - $b[2]) < 60;
for ($o = 1; $o <= 8; $o++) {
    $bytes = file_get_contents($fx("orient-$o.jpg"));
    check("EXIF orientation $o read", ExifOrientation::read($bytes) === $o);
    $out = $san->sanitize($fx("orient-$o.jpg"), 'gallery', $tmp."/o$o");
    $im = imagecreatefromwebp($out->path);
    $px = function (int $x, int $y) use ($im): array {
        $c = imagecolorat($im, $x, $y);

        return [($c >> 16) & 255, ($c >> 8) & 255, $c & 255];
    };
    $ok = $out->width === 240 && $out->height === 120
        && $near($px(60, 30), $expect['TL']) && $near($px(180, 30), $expect['TR'])
        && $near($px(60, 90), $expect['BL']) && $near($px(180, 90), $expect['BR']);
    check("orientation $o restored to the displayed picture", $ok, "{$out->width}x{$out->height}");
}

section('ImageSanitizer: privacy and safety');
$orig = file_get_contents($fx('orient-6.jpg'));
check('fixture really carries EXIF + a comment marker', str_contains($orig, "Exif\0\0") && str_contains($orig, 'SECRET-LOCATION-MARKER'));
$out = $san->sanitize($fx('orient-6.jpg'), 'gallery', $tmp.'/clean');
$cleanBytes = file_get_contents($out->path);
check('output has no EXIF block, GPS tag text or comment', ! str_contains($cleanBytes, "Exif\0\0") && ! str_contains($cleanBytes, 'SECRET-LOCATION-MARKER') && ! str_contains($cleanBytes, "\xFF\xE1"));
check('output is WebP', str_starts_with($cleanBytes, 'RIFF') && substr($cleanBytes, 8, 4) === 'WEBP' && $out->mime === 'image/webp');
$poly = $san->sanitize($fx('polyglot.jpg'), 'gallery', $tmp.'/poly');
check('polyglot: trailing PHP code does not survive', ! str_contains(file_get_contents($poly->path), '<?php'));
$big = $san->sanitize($fx('big.jpg'), 'gallery', $tmp.'/big');
check('3000x2000 downscaled to 2400 longest edge, ratio kept', $big->width === 2400 && $big->height === 1600, "{$big->width}x{$big->height}");
$small = $san->sanitize($fx('ok-800x600.jpg'), 'gallery', $tmp.'/small');
check('never upscaled', $small->width === 800 && $small->height === 600);
$qr = $san->sanitize($fx('transparent.png'), 'qr', $tmp.'/qr');
$qrIm = imagecreatefrompng($qr->path);
check('qr stored as lossless PNG', $qr->extension === 'png' && $qr->mime === 'image/png');
check('PNG transparency preserved (corner pixel is transparent)', ((imagecolorat($qrIm, 2, 2) >> 24) & 127) === 127);
check('opaque pixels preserved', ((imagecolorat($qrIm, 150, 150) >> 24) & 127) === 0);
$threw = false;
try {
    $san->sanitize($fx('truncated.jpg'), 'gallery', $tmp.'/trunc');
} catch (SanitizeException) {
    $threw = true;
}
check('truncated JPEG fails cleanly with SanitizeException', $threw || is_file($tmp.'/trunc.webp'));
$threw = false;
try {
    $san->sanitize($fx('fake-php.jpg'), 'gallery', $tmp.'/fake');
} catch (SanitizeException) {
    $threw = true;
}
check('non-image content refuses to sanitize', $threw);
check('sanitizer leaves the source untouched', file_get_contents($fx('orient-6.jpg')) === $orig);

// ---------------------------------------------------------------- MED-05
section('AudioInspector (MED-05)');
$audio = new AudioInspector($limits['audio']);
$mp3 = $tmp.'/song.mp3';
file_put_contents($mp3, "\xFF\xFB\x90\x64".str_repeat("\0", 1500));
$id3 = $tmp.'/tagged.mp3';
file_put_contents($id3, "ID3\x03\x00\x00\x00\x00\x00\x00"."\xFF\xFB\x90\x64".str_repeat("\0", 1500)); // ID3v2 header with size 0, frame follows
$ogg = $tmp.'/song.ogg';
file_put_contents($ogg, "OggS\x00\x02".str_repeat("\0", 8)."\x01\x02\x03\x04\x00\x00\x00\x00\x00\x00\x00\x00\x01\x1e\x01vorbis".str_repeat("\0", 200));
$exe = $tmp.'/evil.mp3';
file_put_contents($exe, "MZ\x90\x00".str_repeat("\0", 300));
$wav = $tmp.'/a.wav';
file_put_contents($wav, "RIFF\x24\x00\x00\x00WAVEfmt ".str_repeat("\0", 40));
$r = $audio->inspect($mp3, 'lagu.mp3');
check('mp3 (frame sync) accepted', $r->ok, json_encode($r->errors).' mime='.$r->mime);
$r = $audio->inspect($id3, 'lagu.mp3');
check('mp3 with ID3 tag accepted', $r->ok, json_encode($r->errors).' mime='.$r->mime);
$r = $audio->inspect($ogg, 'lagu.ogg');
check('ogg accepted', $r->ok, json_encode($r->errors).' mime='.$r->mime);
check('executable renamed .mp3 rejected', ! $audio->inspect($exe, 'evil.mp3')->ok);
check('wav rejected', ! $audio->inspect($wav, 'a.wav')->ok);
check('extension must be mp3/ogg', $audio->inspect($mp3, 'lagu.exe')->has('bad_extension'));
check('extension must match content', $audio->inspect($mp3, 'lagu.ogg')->has('extension_mismatch'));
$big = $tmp.'/big.mp3';
file_put_contents($big, "\xFF\xFB\x90\x64".str_repeat("\0", 7 * 1024 * 1024));
check('over size limit', $audio->inspect($big, 'big.mp3')->has('too_large'));
check('empty file', $audio->inspect($tmp.'/zero.mp3', 'zero.mp3')->has('unreadable'));

// cleanup
array_map('unlink', glob($tmp.'/*') ?: []);
@rmdir($tmp);
echo "\n".str_repeat('=', 40)."\n$pass passed, $fail failed\n";
exit($fail === 0 ? 0 : 1);
