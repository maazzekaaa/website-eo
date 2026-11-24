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
        'red' => [[225, 29, 46], [120, 16, 28]],
        'black' => [[40, 86, 170], [30, 40, 80]],
        'maroon' => [[170, 40, 150], [70, 14, 60]],
        'silver' => [[225, 170, 90], [120, 80, 40]],
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
        $clients = collect(['Mitra Nusantara Event', 'Kampus Harapan Bangsa', 'Keluarga Hartono'])
            ->map(fn ($name) => Client::create(['name' => $name, 'description' => 'Data contoh.']));
        $places = ['Gedung Serbaguna, Malang', 'Aula Kampus, Surabaya', 'Taman Kota, Batu', 'Studio Foto, Kediri'];

        $rows = [
            ['Resepsi Taman Malam', 'Wedding', 'red', '2026-08-15', null, 'Sound, Lighting', 'Resepsi di bawah langit terbuka, diterangi cahaya yang hangat dan intim.', 'Kami mengubah taman terbuka menjadi ruang resepsi yang hangat. Lampu ambient keemasan menyusuri pepohonan, panggung akustik menyambut penampilan live, dan tata suara tetap jernih hingga meja terjauh tanpa pernah mengalahkan percakapan para tamu.'],
            ['Gathering Tahunan', 'Corporate', 'black', '2026-07-18', 0, 'Sound, Videotron, Rigging', 'Satu malam penuh apresiasi, dengan videotron indoor sebagai pusat cerita.', 'Panggung utama kami rancang dengan videotron indoor sebagai pusat visual. Presentasi kinerja, pemutaran video, hingga sesi hiburan karyawan mengalir dalam satu alur yang rapi. Rigging dipasang sehari lebih awal agar gladi bersih berjalan tenang, tanpa terburu-buru.'],
            ['Konser Kampus', 'Corporate', 'maroon', '2026-06-20', 1, 'Sound, Lighting, Rigging', 'Sound berdaya besar dan lighting show untuk ribuan suara yang bernyanyi bersama.', 'Panggung band kampus kami bekali sistem sound berdaya besar, rigging truss setinggi enam meter, dan lighting show yang mengikuti setiap lagu. Hasilnya, energi penonton terjaga dari band pembuka hingga encore terakhir.'],
            ['Sesi Foto Produk', 'Corporate', 'silver', '2026-05-12', 2, 'Lighting', 'Cahaya studio yang presisi untuk katalog yang tampil meyakinkan.', 'Lighting studio portabel kami tata langsung di lokasi klien, lengkap dengan softbox dan reflektor. Setiap produk tampil dengan warna akurat dan detail tajam, siap untuk katalog cetak maupun digital.'],
            ['Akad & Resepsi', 'Wedding', 'red', '2026-04-25', null, 'Sound, Multimedia', 'Siaran langsung akad, agar keluarga yang jauh tetap terasa dekat.', 'Tidak semua keluarga bisa hadir, tetapi tak seorang pun harus melewatkan momen sakral ini. Akad nikah kami siarkan langsung dari beberapa sudut kamera, sementara tata suara memastikan setiap kata ijab kabul terdengar jelas.'],
            ['Peluncuran Produk', 'Corporate', 'black', '2026-03-14', 3, 'Videotron, Lighting', 'Videotron outdoor dan lighting dramatis untuk momen reveal yang dinantikan.', 'Momen reveal adalah jantung sebuah peluncuran. Kami membangunnya dengan videotron outdoor beresolusi tinggi dan lighting dramatis yang memuncak tepat saat produk tampil, menghadirkan kesan yang terbawa hingga ke media sosial para tamu.'],
            ['Festival Musik Lokal', 'Corporate', 'maroon', '2026-02-21', 4, 'Sound, Lighting, Videotron', 'Dua panggung, belasan penampil, satu hari tanpa jeda.', 'Kru kami mengatur pergantian sound, lighting, dan videotron antarpenampil dengan presisi di dua panggung sekaligus, sehingga festival mengalir sepanjang hari tanpa waktu kosong.'],
            ['Prewedding Indoor', 'Wedding', 'silver', '2026-01-17', null, 'Lighting', 'Pencahayaan sinematik yang membuat setiap bingkai terasa seperti adegan film.', 'Untuk sesi prewedding di dalam ruangan, kami merancang pencahayaan sinematik dengan kontras lembut dan aksen warna hangat, membingkai cerita pasangan dalam suasana yang romantis dan personal.'],
        ];

        $images = app(ImageService::class);

        foreach ($rows as $i => [$title, $category, $color, $date, $client, $services, $caption, $story]) {
            $portfolio = Portfolio::create([
                'title' => $title, 'slug' => str($title)->slug()->toString(), 'category' => $category, 'folder_color' => $color,
                'event_date' => $date, 'client_id' => $clients[$i % 3]->id, 'location' => $places[$i % 4],
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

    /** Foto contoh: ilustrasi panggung buatan kode, tiap foto berbeda. Ganti dengan foto asli acara. */
    private function placeholder(string $color, int $n): UploadedFile
    {
        mt_srand(crc32($color.$n));
        [$from, $to] = self::GRADIENTS[$color];
        $width = 1600;
        $height = 1200;
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);

        // Latar: dari warna acara ke gelap.
        for ($y = 0; $y < $height; $y += 3) {
            $k = $y / $height;
            $line = imagecolorallocate($image, ...array_map(fn ($a, $b) => (int) ($a * (1 - $k) * 0.7 + $b * $k * 0.5), $from, $to));
            imagefilledrectangle($image, 0, $y, $width, $y + 2, $line);
        }

        // Layar LED di tengah panggung.
        $screenW = mt_rand(520, 820);
        $screenX = (int) (($width - $screenW) / 2) + mt_rand(-140, 140);
        $screen = imagecolorallocatealpha($image, min(255, $from[0] + 40), min(255, $from[1] + 40), min(255, $from[2] + 40), 70);
        imagefilledrectangle($image, $screenX, 330, $screenX + $screenW, 760, $screen);

        // Lampu sorot dari rangka atas.
        $truss = imagecolorallocate($image, 18, 18, 20);
        imagefilledrectangle($image, 0, 118, $width, 150, $truss);
        for ($i = 0, $beams = mt_rand(4, 7); $i < $beams; $i++) {
            $x = (int) (($i + 0.5) * $width / $beams) + mt_rand(-60, 60);
            $spread = mt_rand(120, 240);
            $lean = mt_rand(-420, 420);
            $beam = imagecolorallocatealpha($image, min(255, 180 + $from[0] / 4), min(255, 170 + $from[1] / 4), min(255, 170 + $from[2] / 4), mt_rand(92, 108));
            imagefilledpolygon($image, [$x, 150, $x + $lean - $spread, 1000, $x + $lean + $spread, 1000], $beam);
            imagefilledellipse($image, $x, 158, 34, 34, imagecolorallocate($image, 255, 245, 225));
        }

        // Lantai panggung dan penonton.
        $floor = imagecolorallocatealpha($image, 8, 8, 10, 30);
        imagefilledrectangle($image, 0, 860, $width, $height, $floor);
        $crowd = imagecolorallocate($image, 6, 6, 8);
        for ($x = -20; $x < $width; $x += mt_rand(38, 64)) {
            $top = 1010 + mt_rand(-34, 40);
            imagefilledellipse($image, $x, $top, 58, 66, $crowd);
            imagefilledrectangle($image, $x - 44, $top + 28, $x + 44, $height, $crowd);
        }

        $path = tempnam(sys_get_temp_dir(), 'fzm');
        imagepng($image, $path);
        imagedestroy($image);

        return new UploadedFile($path, "demo-{$n}.png", 'image/png', null, true);
    }
}
