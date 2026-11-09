<?php

namespace Tests\Feature;

use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{
    public function test_image_service_menghasilkan_webp_dan_thumbnail(): void
    {
        Storage::fake('public');

        $path = app(ImageService::class)->store(UploadedFile::fake()->image('foto.png', 3000, 2000), 'portfolios');

        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);
        $thumb = preg_replace('/\.webp$/', '_thumb.webp', $path);
        Storage::disk('public')->assertExists($thumb);
        [$w] = getimagesizefromstring(Storage::disk('public')->get($path));
        [$tw] = getimagesizefromstring(Storage::disk('public')->get($thumb));
        $this->assertSame(1920, $w);
        $this->assertSame(480, $tw);
        $this->assertSame('RIFF', substr(Storage::disk('public')->get($path), 0, 4));
    }

    public function test_hapus_menghapus_keduanya(): void
    {
        Storage::fake('public');
        $service = app(ImageService::class);
        $path = $service->store(UploadedFile::fake()->image('foto.jpg', 100, 100), 'x');

        $service->delete($path);

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_gambar_terlalu_besar_ditolak_sebelum_diolah(): void
    {
        Storage::fake('public');
        // Header PNG yang mengaku berukuran 20000 x 20000 piksel.
        $header = "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR".pack('NN', 20000, 20000)."\x08\x06\x00\x00\x00\x00\x00\x00\x00";

        $this->expectExceptionMessage('Resolusi gambar terlalu besar');
        app(ImageService::class)->store(UploadedFile::fake()->createWithContent('besar.png', $header), 'x');
    }

    public function test_berkas_rusak_ditolak(): void
    {
        Storage::fake('public');

        $this->expectException(ValidationException::class);
        app(ImageService::class)->store(UploadedFile::fake()->createWithContent('rusak.jpg', 'bukan gambar'), 'x');
    }
}
