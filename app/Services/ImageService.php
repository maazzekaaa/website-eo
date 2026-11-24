<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImageService
{
    private const MAX_WIDTH = 1920;

    private const THUMB_WIDTH = 480;

    /** Batas piksel: gambar lebih besar menghabiskan memori PHP saat dibuka. */
    private const MAX_PIXELS = 50_000_000;

    /** Simpan sebagai WebP plus thumbnail `{nama}_thumb.webp`, lalu kembalikan path-nya. */
    public function store(UploadedFile $file, string $dir, string $disk = 'public'): string
    {
        [$width, $height] = @getimagesize($file->getRealPath()) ?: [0, 0];
        if ($width * $height > self::MAX_PIXELS) {
            throw ValidationException::withMessages(['image' => 'Resolusi gambar terlalu besar (maks. 50 megapiksel).']);
        }

        ini_set('memory_limit', '512M');
        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $image instanceof GdImage) {
            throw ValidationException::withMessages(['image' => 'Berkas gambar tidak dapat dibaca.']);
        }

        $image = $this->fixOrientation($image, $file);
        imagepalettetotruecolor($image);
        imagesavealpha($image, true);

        $name = trim($dir, '/').'/'.Str::uuid();
        Storage::disk($disk)->put("{$name}.webp", $this->encode($this->resize($image, self::MAX_WIDTH)));
        Storage::disk($disk)->put("{$name}_thumb.webp", $this->encode($this->resize($image, self::THUMB_WIDTH)));

        return "{$name}.webp";
    }

    public function delete(?string $path, string $disk = 'public'): void
    {
        if ($path) {
            Storage::disk($disk)->delete([$path, preg_replace('/\.webp$/', '_thumb.webp', $path)]);
        }
    }

    private function resize(GdImage $image, int $width): GdImage
    {
        if (imagesx($image) <= $width) {
            return $image;
        }
        $resized = imagescale($image, $width, -1, IMG_BICUBIC);
        if (! $resized instanceof GdImage) {
            // Sebagian build GD tidak mendukung mode BICUBIC dan mengembalikan false: ubah ukuran manual.
            $height = max(1, (int) round(imagesy($image) * $width / imagesx($image)));
            $resized = imagecreatetruecolor($width, $height);
            imagealphablending($resized, false);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
        }
        imagesavealpha($resized, true);

        return $resized;
    }

    private function encode(GdImage $image): string
    {
        ob_start();
        imagewebp($image, null, 82);

        return (string) ob_get_clean();
    }

    /** Foto dari ponsel sering menyimpan arah putar di EXIF, yang hilang saat dikonversi. */
    private function fixOrientation(GdImage $image, UploadedFile $file): GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array($file->getMimeType(), ['image/jpeg'], true)) {
            return $image;
        }
        $orientation = @exif_read_data($file->getRealPath())['Orientation'] ?? 1;

        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }
}
