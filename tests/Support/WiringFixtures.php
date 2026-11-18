<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Illuminate\Http\UploadedFile;

/** Small, dependency-free fixtures for the GST/MED wiring tests (real files on disk, so finfo sees real bytes). */
final class WiringFixtures
{
    public const MARK_EXIF = 'SECRET-EXIF-MARKER';

    public const MARK_COMMENT = 'SECRET-COMMENT-MARKER';

    public const MARK_POLYGLOT = '<?php echo "pwned"; ?>';

    /** @return array{0:User,1:Invitation} */
    public static function owned(InvitationStatus $status = InvitationStatus::Published): array
    {
        $user = User::factory()->create();
        $invitation = Invitation::factory()->for($user)->create(['status' => $status]);

        return [$user, $invitation];
    }

    public static function upload(string $contents, string $clientName): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'wf-') ?: throw new \RuntimeException('tempnam failed');
        file_put_contents($path, $contents);

        return new UploadedFile($path, $clientName, null, null, true);
    }

    /** JPEG with EXIF orientation, a marker string inside APP1, a COM segment and PHP appended after EOI. */
    public static function jpeg(int $w = 1300, int $h = 700, int $orientation = 1, bool $dirty = true): string
    {
        $im = imagecreatetruecolor($w, $h);
        imagefilledrectangle($im, 0, 0, $w, $h, (int) imagecolorallocate($im, 200, 40, 90));
        ob_start();
        imagejpeg($im, null, 90);
        $jpeg = (string) ob_get_clean();
        imagedestroy($im);
        if (! $dirty) {
            return $jpeg;
        }

        $tiff = 'II'."\x2A\x00"."\x08\x00\x00\x00"        // header, IFD0 at offset 8
            ."\x01\x00"                                     // one entry
            ."\x12\x01\x03\x00\x01\x00\x00\x00".pack('v', $orientation)."\x00\x00" // Orientation, SHORT, 1
            ."\x00\x00\x00\x00".self::MARK_EXIF;            // next IFD = 0, then junk we expect to vanish
        $app1 = "Exif\x00\x00".$tiff;
        $segment = "\xFF\xE1".pack('n', strlen($app1) + 2).$app1;
        $comment = self::MARK_COMMENT;
        $com = "\xFF\xFE".pack('n', strlen($comment) + 2).$comment;

        return substr($jpeg, 0, 2).$segment.$com.substr($jpeg, 2).self::MARK_POLYGLOT;
    }

    public static function png(int $w = 600, int $h = 600, bool $alpha = false): string
    {
        $im = imagecreatetruecolor($w, $h);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        imagefill($im, 0, 0, (int) imagecolorallocatealpha($im, 0, 0, 0, $alpha ? 127 : 0));
        imagefilledrectangle($im, 10, 10, $w - 10, $h - 10, (int) imagecolorallocate($im, 20, 20, 20));
        ob_start();
        imagepng($im);
        $out = (string) ob_get_clean();
        imagedestroy($im);

        return $out;
    }

    public static function gif(): string
    {
        $im = imagecreatetruecolor(800, 800);
        ob_start();
        imagegif($im);

        return (string) ob_get_clean();
    }

    public static function svg(): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="900" height="900"><script>alert(1)</script></svg>';
    }

    public static function mp3(): string
    {
        return 'ID3'."\x03\x00\x00\x00\x00\x00\x00".str_repeat("\xFF\xFB\x90\x00".str_repeat("\x00", 413), 8);
    }

    public static function ogg(): string
    {
        // capture pattern, version 0, BOS flag, granule(8)+serial(4)+sequence(4)+crc(4), 1 segment of 30 bytes, Vorbis ident header
        return 'OggS'."\x00\x02".str_repeat("\x00", 20)."\x01\x1E"."\x01vorbis".str_repeat("\x00", 200);
    }
}
