<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Proses unggahan gambar: re-encode (membuang payload berbahaya/EXIF),
 * resize, dan konversi ke WebP agar halaman cepat.
 */
class ImageService
{
    public static function store(UploadedFile $file, string $dir, int $maxWidth = 1600, string $format = 'webp', string $disk = 'public', int $quality = 80): string
    {
        $info = @getimagesize($file->getRealPath());
        if (! $info) {
            throw new RuntimeException('File bukan gambar yang valid.');
        }

        [$w, $h, $type] = $info;
        $src = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($file->getRealPath()),
            IMAGETYPE_PNG => @imagecreatefrompng($file->getRealPath()),
            IMAGETYPE_WEBP => @imagecreatefromwebp($file->getRealPath()),
            IMAGETYPE_GIF => @imagecreatefromgif($file->getRealPath()),
            default => false,
        };
        if (! $src) {
            throw new RuntimeException('Format gambar tidak didukung. Gunakan JPG, PNG, atau WebP.');
        }

        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $src = static::fixOrientation($src, $file->getRealPath());
            $w = imagesx($src);
            $h = imagesy($src);
        }

        $ratio = $w > $maxWidth ? $maxWidth / $w : 1;
        $nw = max(1, (int) round($w * $ratio));
        $nh = max(1, (int) round($h * $ratio));

        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 255, 255, 255, 127));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        ob_start();
        match ($format) {
            'png' => imagepng($dst, null, 7),
            'jpg' => imagejpeg(static::flatten($dst, $nw, $nh), null, $quality),
            default => imagewebp($dst, null, $quality),
        };
        $binary = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        $path = trim($dir, '/').'/'.date('Y/m').'/'.Str::random(32).'.'.$format;
        Storage::disk($disk)->put($path, $binary);

        return $path;
    }

    /** Salin file agar data duplikat tidak berbagi file yang sama. */
    public static function duplicate(?string $path, string $disk = 'public'): ?string
    {
        if (! $path || ! Storage::disk($disk)->exists($path)) {
            return null;
        }
        $new = dirname($path).'/'.Str::random(32).'.'.pathinfo($path, PATHINFO_EXTENSION);
        Storage::disk($disk)->copy($path, $new);

        return $new;
    }

    public static function delete(?string $path, string $disk = 'public'): void
    {
        if ($path && ! str_starts_with($path, 'http') && ! str_starts_with($path, '/')) {
            Storage::disk($disk)->delete($path);
        }
    }

    protected static function flatten($img, int $w, int $h)
    {
        $bg = imagecreatetruecolor($w, $h);
        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
        imagecopy($bg, $img, 0, 0, 0, 0, $w, $h);

        return $bg;
    }

    protected static function fixOrientation($img, string $path)
    {
        $exif = @exif_read_data($path);
        $orientation = $exif['Orientation'] ?? 1;

        return match ((int) $orientation) {
            3 => imagerotate($img, 180, 0),
            6 => imagerotate($img, -90, 0),
            8 => imagerotate($img, 90, 0),
            default => $img,
        };
    }
}
