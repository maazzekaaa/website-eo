<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        Admin::updateOrCreate(['email' => 'admin@fzm.id'], ['name' => 'Admin FZM', 'password' => 'admin123']);

        Setting::query()->firstOrCreate([], [
            'company_name' => 'FZM Production Mediatama',
            'address' => '[Alamat lengkap FZM]',
            'email' => '[email@fzm]',
            'wa_number' => '62XXXXXXXXXX',
            'npwp' => '[nomor NPWP]',
            'bank_name' => '[Nama Bank]',
            'bank_account' => '[nomor rekening]',
            'bank_holder' => 'FZM Production Mediatama',
            'tax_enabled' => false,
            'tax_rate' => 11,
            'hero_title' => 'Ada cerita di balik setiap perayaan.',
            'hero_subtitle' => 'Mari tulis dan wujudkan ceritamu bersama kami.',
            'about' => "FZM Production Mediatama adalah event organizer dan penyedia produksi panggung yang lahir dari satu keyakinan sederhana: setiap perayaan layak dikenang dengan sempurna.\n\nKami menangani sound system, lighting, videotron, rigging, hingga multimedia broadcasting dalam satu tim yang solid, mulai dari survei lokasi dan perencanaan teknis sampai acara usai. Tanpa perlu berkoordinasi dengan banyak vendor, Anda cukup fokus menikmati momen.\n\nDari resepsi pernikahan yang intim hingga gathering perusahaan berskala besar, kami hadir dengan peralatan terawat, kru berpengalaman, dan harga yang terbuka sejak awal.",
            'opening_hours' => '[Jam operasional]',
        ]);

        $tiers = [
            ['sound', 'Sound System', 'Suara jernih yang menjangkau setiap sudut ruang, dari akad yang khidmat hingga konser yang menggelegar.', [3000000, 5000000, 7000000, 10000000], null],
            ['lighting', 'Lighting', 'Tata cahaya yang membangun suasana: hangat untuk momen intim, dramatis saat panggung menuntut perhatian.', [1500000, 2250000, 3000000], null],
            ['multimedia', 'Multimedia Broadcasting System', 'Abadikan dan siarkan acara secara langsung dengan kamera multi-sudut, switcher, dan streaming profesional.', [1500000, 2500000, 3000000], 'baru'],
        ];
        $units = [
            ['videotron-indoor', 'Videotron Indoor', 'Layar LED beresolusi tinggi untuk latar panggung dan presentasi yang memukau di dalam ruangan.', 'm²', 400000],
            ['videotron-outdoor', 'Videotron Outdoor', 'Layar LED berkecerahan tinggi yang tetap tajam di bawah terik siang maupun langit malam.', 'm²', 650000],
            ['rigging', 'Rigging', 'Struktur truss yang kokoh dan teruji untuk menopang lighting, sound, dan layar dengan aman.', 'm', 150000],
        ];

        $order = 0;
        foreach ($tiers as [$slug, $name, $description, $prices, $badge]) {
            $service = Service::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'description' => $description, 'pricing_type' => 'tier',
                'badge' => $badge, 'sort_order' => ++$order,
            ]);
            foreach ($prices as $i => $price) {
                $service->options()->updateOrCreate(['name' => 'Paket '.($i + 1)], ['price' => $price, 'sort_order' => $i + 1]);
            }
        }
        foreach ($units as [$slug, $name, $description, $unit, $price]) {
            Service::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'description' => $description, 'pricing_type' => 'per_unit',
                'unit_label' => $unit, 'unit_price' => $price, 'sort_order' => ++$order,
            ]);
        }
    }
}
