<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Data awal: admin, pengaturan, layanan & harga PRD FR-3. Tanpa data demo. */
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
            'hero_title' => "Panggung yang hidup,\nacara yang dikenang.",
            'hero_subtitle' => 'Sound, lighting, videotron, rigging, dan multimedia untuk pernikahan, acara korporat, konser, hingga photoshoot.',
            'about' => 'FZM Production Mediatama adalah event organizer dan penyedia produksi panggung: sound, lighting, videotron, rigging, dan multimedia untuk pernikahan, acara korporat, konser, hingga photoshoot.',
            'opening_hours' => '[Jam operasional]',
        ]);

        $tiers = [
            ['sound', 'Sound System', 'Tata suara untuk acara kecil hingga panggung besar.', [3000000, 5000000, 7000000, 10000000], null],
            ['lighting', 'Lighting', 'Pencahayaan panggung, ambience, dan efek show.', [1500000, 2250000, 3000000], null],
            ['multimedia', 'Multimedia Broadcasting System', 'Kamera, switcher, dan siaran langsung acara.', [1500000, 2500000, 3000000], 'baru'],
        ];
        $units = [
            ['videotron-indoor', 'Videotron Indoor', 'Layar LED untuk ruangan dan latar panggung.', 'm²', 400000],
            ['videotron-outdoor', 'Videotron Outdoor', 'Layar LED terang untuk luar ruangan.', 'm²', 650000],
            ['rigging', 'Rigging', 'Struktur truss untuk lighting, sound, dan layar.', 'm', 150000],
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
