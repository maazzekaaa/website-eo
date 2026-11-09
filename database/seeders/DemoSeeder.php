<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Portfolio;
use App\Models\Service;
use App\Services\BookingService;
use App\Services\CartPricingService;
use App\Services\ImageService;
use App\Services\PaymentService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

/**
 * Data contoh untuk demo: portofolio, klien, booking, dan pendapatan.
 * Jalankan terpisah setelah DatabaseSeeder. Jangan dipakai di data produksi.
 */
class DemoSeeder extends Seeder
{
    private const GRADIENTS = [
        'red' => [[225, 29, 46], [60, 8, 14]],
        'black' => [[58, 58, 58], [13, 13, 13]],
        'maroon' => [[142, 15, 27], [32, 6, 10]],
        'silver' => [[190, 190, 195], [90, 90, 96]],
    ];

    public function run(): void
    {
        if (Portfolio::exists() || Booking::exists()) {
            $this->command?->warn('Data demo dilewati: database sudah berisi data.');

            return;
        }

        $this->seedPortfolios();
        $this->seedBookings();
    }

    private function seedPortfolios(): void
    {
        $clients = collect(range(1, 6))->map(fn ($n) => Client::create(['name' => "Mitra {$n}", 'description' => 'Data contoh.']));

        $rows = [
            ['Resepsi Taman Malam', 'Wedding', 'red', '2026-08-15', null, 'Sound, Lighting', 'Tata suara dan cahaya hangat untuk resepsi outdoor.', 'Tata suara dan pencahayaan hangat untuk resepsi outdoor, lengkap dengan panggung akustik.'],
            ['Gathering Tahunan', 'Corporate', 'black', '2026-07-18', 0, 'Sound, Videotron, Rigging', 'Panggung utama dengan videotron indoor.', 'Panggung utama dengan videotron indoor untuk presentasi dan sesi hiburan karyawan.'],
            ['Konser Kampus', 'Concert', 'maroon', '2026-06-20', 1, 'Sound, Lighting, Rigging', 'Sound berdaya besar dan lighting show.', 'Sistem sound berdaya besar, rigging truss, dan lighting show untuk penampilan band.'],
            ['Sesi Foto Produk', 'Photoshoot', 'silver', '2026-05-12', 2, 'Lighting', 'Lighting studio portabel untuk katalog.', 'Lighting studio portabel untuk pemotretan katalog produk.'],
            ['Akad & Resepsi', 'Wedding', 'red', '2026-04-25', null, 'Sound, Multimedia', 'Siaran langsung akad untuk keluarga jauh.', 'Siaran langsung akad untuk keluarga yang tidak dapat hadir, dengan multimedia broadcasting.'],
            ['Peluncuran Produk', 'Corporate', 'black', '2026-03-14', 3, 'Videotron, Lighting', 'Videotron outdoor untuk momen reveal.', 'Videotron outdoor dan lighting dramatis untuk momen reveal produk.'],
            ['Festival Musik Lokal', 'Concert', 'maroon', '2026-02-21', 4, 'Sound, Lighting, Videotron', 'Dukungan produksi untuk dua panggung.', 'Dukungan produksi penuh untuk dua panggung sepanjang hari.'],
            ['Prewedding Indoor', 'Photoshoot', 'silver', '2026-01-17', null, 'Lighting', 'Pencahayaan sinematik di dalam ruangan.', 'Pencahayaan sinematik untuk sesi foto prewedding di dalam ruangan.'],
        ];

        $images = app(ImageService::class);

        foreach ($rows as $i => [$title, $category, $color, $date, $client, $services, $caption, $story]) {
            $portfolio = Portfolio::create([
                'title' => $title, 'slug' => str($title)->slug()->toString(), 'category' => $category, 'folder_color' => $color,
                'event_date' => $date, 'client_id' => $client === null ? null : $clients[$client]->id, 'location' => '[Lokasi]',
                'services' => $services, 'caption' => $caption, 'story' => $story, 'is_featured' => true,
            ]);

            for ($n = 1, $count = 3 + $i % 4; $n <= $count; $n++) {
                $portfolio->images()->create(['path' => $images->store($this->placeholder($color, $n), 'portfolios'), 'sort_order' => $n]);
            }
        }
    }

    private function seedBookings(): void
    {
        $today = now()->startOfDay();
        $on = fn (int $days) => $today->copy()->addDays($days)->toDateString();

        // Arsip 12 bulan terakhir agar grafik pendapatan terisi. Dibuat lebih dulu agar
        // booking contoh utama tampil paling atas di daftar.
        $archive = [];
        foreach (range(1, 12) as $back) {
            $date = $today->copy()->startOfMonth()->subMonthsNoOverflow($back)->addDays(9 + $back);
            $archive[] = ['Arsip Klien '.$back, $date->toDateString(), ['sound' => 'Paket '.(1 + $back % 4), 'lighting' => 'Paket '.(1 + $back % 3)], ['rigging' => 3 + $back], 'completed', [['full', $today->diffInDays($date, true) + 5]]];
        }

        $bookings = [...$archive,

            ['Andi Pratama', $on(34), ['sound' => 'Paket 2'], ['videotron-indoor' => 12, 'rigging' => 12], 'pending', []],
            ['CV Cahaya Abadi', $on(35), ['sound' => 'Paket 2'], ['videotron-indoor' => 12, 'rigging' => 12], 'pending', [['dp', 0]]],
            ['Sari & Dimas', $on(40), ['sound' => 'Paket 2', 'lighting' => 'Paket 2', 'multimedia' => 'Paket 1'], [], 'confirmed', [['dp', 4]]],
            ['Panitia Konser Kampus', $on(33), ['sound' => 'Paket 4', 'lighting' => 'Paket 3'], ['videotron-outdoor' => 12, 'rigging' => 20], 'confirmed', [['dp', 8], ['full', 2]]],
            ['Keluarga Hartono', $on(26), ['sound' => 'Paket 2', 'lighting' => 'Paket 3'], [], 'confirmed', [['full', 15]]],
            ['Gathering Kantor', $on(5), ['sound' => 'Paket 2', 'multimedia' => 'Paket 1'], [], 'confirmed', [['dp', 20]]],
            ['Maya Lestari', $on(-7), ['sound' => 'Paket 1', 'lighting' => 'Paket 2'], ['rigging' => 5], 'completed', [['dp', 22], ['full', 7]]],
        ];

        foreach ($bookings as $i => [$name, $date, $tiers, $units, $status, $payments]) {
            $this->booking($name, $date, $tiers, $units, $status, $payments, $i);
        }
    }

    private function booking(string $name, string $date, array $tiers, array $units, string $status, array $payments, int $i): void
    {
        $items = [];
        foreach ($tiers as $slug => $option) {
            $items[$slug] = Service::where('slug', $slug)->firstOrFail()->options()->where('name', $option)->firstOrFail()->id;
        }

        $bookingService = app(BookingService::class);
        $paymentService = app(PaymentService::class);
        $admin = Admin::first()?->id;

        $booking = $bookingService->create([
            'customer_name' => $name, 'customer_wa' => '0812345'.str_pad((string) $i, 5, '0', STR_PAD_LEFT), 'event_date' => $date,
            'event_time' => '18:00', 'event_location' => '[Gedung / alamat acara]', 'note' => 'Data contoh.',
        ], app(CartPricingService::class)->price($items, $units));

        foreach ($payments as [$kind, $daysAgo]) {
            $paymentService->record($booking, [
                'amount' => $kind === 'dp' ? $booking->dp_amount : $booking->restAmount(),
                'type' => $kind === 'dp' ? 'dp' : 'pelunasan', 'method' => $i % 3 ? 'transfer' : 'tunai',
                'paid_at' => now()->subDays($daysAgo)->toDateString(),
            ], $admin);
        }

        if (in_array($status, ['confirmed', 'completed'], true)) {
            $bookingService->confirm($booking->fresh());
        }
        if ($status === 'completed') {
            $bookingService->complete($booking->fresh());
        }
    }

    /** Foto contoh: gradasi warna folder, tiap foto sedikit berbeda. */
    private function placeholder(string $color, int $n): UploadedFile
    {
        [$from, $to] = self::GRADIENTS[$color];
        $width = 1200;
        $height = 800;
        $image = imagecreatetruecolor($width, $height);

        for ($y = 0; $y < $height; $y += 4) {
            $k = $y / $height;
            $line = imagecolorallocate($image, ...array_map(fn ($a, $b) => (int) ($a + ($b - $a) * $k), $from, $to));
            imagefilledrectangle($image, 0, $y, $width, $y + 3, $line);
        }
        $spot = imagecolorallocatealpha($image, 255, 255, 255, 105);
        imagefilledellipse($image, 180 + $n * 170, 240 + ($n % 3) * 110, 340, 340, $spot);

        $path = tempnam(sys_get_temp_dir(), 'fzm');
        imagepng($image, $path);

        return new UploadedFile($path, "demo-{$n}.png", 'image/png', null, true);
    }
}
