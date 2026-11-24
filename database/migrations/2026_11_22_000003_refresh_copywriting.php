<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Copywriting baru untuk deskripsi layanan, profil perusahaan, dan portofolio contoh.
 * Hanya mengganti teks yang masih sama persis dengan teks bawaan lama,
 * sehingga tulisan yang sudah diubah admin tidak tertimpa.
 */
return new class extends Migration
{
    public function up(): void
    {
        $services = [
            'sound' => ['Tata suara untuk acara kecil hingga panggung besar.', 'Suara jernih yang menjangkau setiap sudut ruang, dari akad yang khidmat hingga konser yang menggelegar.'],
            'lighting' => ['Pencahayaan panggung, ambience, dan efek show.', 'Tata cahaya yang membangun suasana: hangat untuk momen intim, dramatis saat panggung menuntut perhatian.'],
            'multimedia' => ['Kamera, switcher, dan siaran langsung acara.', 'Abadikan dan siarkan acara secara langsung dengan kamera multi-sudut, switcher, dan streaming profesional.'],
            'videotron-indoor' => ['Layar LED untuk ruangan dan latar panggung.', 'Layar LED beresolusi tinggi untuk latar panggung dan presentasi yang memukau di dalam ruangan.'],
            'videotron-outdoor' => ['Layar LED terang untuk luar ruangan.', 'Layar LED berkecerahan tinggi yang tetap tajam di bawah terik siang maupun langit malam.'],
            'rigging' => ['Struktur truss untuk lighting, sound, dan layar.', 'Struktur truss yang kokoh dan teruji untuk menopang lighting, sound, dan layar dengan aman.'],
        ];
        foreach ($services as $slug => [$old, $new]) {
            DB::table('services')->where('slug', $slug)->where('description', $old)->update(['description' => $new]);
        }

        $portfolios = [
            ['Resepsi Taman Malam', 'Tata suara dan cahaya hangat untuk resepsi outdoor.', 'Resepsi di bawah langit terbuka, diterangi cahaya yang hangat dan intim.', 'Kami mengubah taman terbuka menjadi ruang resepsi yang hangat. Lampu ambient keemasan menyusuri pepohonan, panggung akustik menyambut penampilan live, dan tata suara tetap jernih hingga meja terjauh tanpa pernah mengalahkan percakapan para tamu.'],
            ['Gathering Tahunan', 'Panggung utama dengan videotron indoor.', 'Satu malam penuh apresiasi, dengan videotron indoor sebagai pusat cerita.', 'Panggung utama kami rancang dengan videotron indoor sebagai pusat visual. Presentasi kinerja, pemutaran video, hingga sesi hiburan karyawan mengalir dalam satu alur yang rapi. Rigging dipasang sehari lebih awal agar gladi bersih berjalan tenang, tanpa terburu-buru.'],
            ['Konser Kampus', 'Sound berdaya besar dan lighting show.', 'Sound berdaya besar dan lighting show untuk ribuan suara yang bernyanyi bersama.', 'Panggung band kampus kami bekali sistem sound berdaya besar, rigging truss setinggi enam meter, dan lighting show yang mengikuti setiap lagu. Hasilnya, energi penonton terjaga dari band pembuka hingga encore terakhir.'],
            ['Sesi Foto Produk', 'Lighting studio portabel untuk katalog.', 'Cahaya studio yang presisi untuk katalog yang tampil meyakinkan.', 'Lighting studio portabel kami tata langsung di lokasi klien, lengkap dengan softbox dan reflektor. Setiap produk tampil dengan warna akurat dan detail tajam, siap untuk katalog cetak maupun digital.'],
            ['Akad & Resepsi', 'Siaran langsung akad untuk keluarga jauh.', 'Siaran langsung akad, agar keluarga yang jauh tetap terasa dekat.', 'Tidak semua keluarga bisa hadir, tetapi tak seorang pun harus melewatkan momen sakral ini. Akad nikah kami siarkan langsung dari beberapa sudut kamera, sementara tata suara memastikan setiap kata ijab kabul terdengar jelas.'],
            ['Peluncuran Produk', 'Videotron outdoor untuk momen reveal.', 'Videotron outdoor dan lighting dramatis untuk momen reveal yang dinantikan.', 'Momen reveal adalah jantung sebuah peluncuran. Kami membangunnya dengan videotron outdoor beresolusi tinggi dan lighting dramatis yang memuncak tepat saat produk tampil, menghadirkan kesan yang terbawa hingga ke media sosial para tamu.'],
            ['Festival Musik Lokal', 'Dukungan produksi untuk dua panggung.', 'Dua panggung, belasan penampil, satu hari tanpa jeda.', 'Kru kami mengatur pergantian sound, lighting, dan videotron antarpenampil dengan presisi di dua panggung sekaligus, sehingga festival mengalir sepanjang hari tanpa waktu kosong.'],
            ['Prewedding Indoor', 'Pencahayaan sinematik di dalam ruangan.', 'Pencahayaan sinematik yang membuat setiap bingkai terasa seperti adegan film.', 'Untuk sesi prewedding di dalam ruangan, kami merancang pencahayaan sinematik dengan kontras lembut dan aksen warna hangat, membingkai cerita pasangan dalam suasana yang romantis dan personal.'],
        ];
        foreach ($portfolios as [$title, $oldCaption, $caption, $story]) {
            DB::table('portfolios')->where('title', $title)->where('caption', $oldCaption)->update(['caption' => $caption, 'story' => $story]);
        }

        DB::table('settings')->where('about', 'FZM Production Mediatama adalah event organizer dan penyedia produksi panggung: sound, lighting, videotron, rigging, dan multimedia untuk pernikahan, acara korporat, konser, hingga photoshoot.')->update(['about' => "FZM Production Mediatama adalah event organizer dan penyedia produksi panggung yang lahir dari satu keyakinan sederhana: setiap perayaan layak dikenang dengan sempurna.\n\nKami menangani sound system, lighting, videotron, rigging, hingga multimedia broadcasting dalam satu tim yang solid, mulai dari survei lokasi dan perencanaan teknis sampai acara usai. Tanpa perlu berkoordinasi dengan banyak vendor, Anda cukup fokus menikmati momen.\n\nDari resepsi pernikahan yang intim hingga gathering perusahaan berskala besar, kami hadir dengan peralatan terawat, kru berpengalaman, dan harga yang terbuka sejak awal."]);
        Cache::forget('settings.current');
    }

    public function down(): void
    {
        // Teks lama tidak dikembalikan.
    }
};
