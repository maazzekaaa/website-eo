# CLAUDE.md — Website Event Organizer FZM Production Mediatama

Konteks proyek untuk Claude Code. Baca ini dulu sebelum mengerjakan apa pun.
Bahasa kerja: **Indonesia** (UI, komentar kode, pesan commit).

## Ringkasan
Website resmi EO **FZM Production Mediatama** (tugas Kelompok 12: Gigih, Dhimas).
- Portofolio berupa 4 kartu acara (Pernikahan & Korporat) + strip logo klien/mitra di beranda.
- Katalog layanan & harga, pemesanan **tanpa login** dengan keranjang multi-layanan dan cek tanggal → diteruskan ke WhatsApp.
- Panel admin: login, dashboard pendapatan, kelola booking & pembayaran (DP/pelunasan), invoice & kuitansi.

Dokumen lengkap: `docs/PRD.md` (v0.2) dan `docs/ALUR-PROGRAM.md` (v0.2). Panduan frontend: `docs/FRONTEND.md`.

## Status saat ini
- Aplikasi Laravel 13 + MySQL. Lihat `README.md` untuk cara menjalankan.
- Rancangan: `docs/superpowers/specs/2026-11-09-laravel-migration-design.md`.

## Struktur
```
app/Services/         logika bisnis: harga, booking, pembayaran, nomor dokumen, pendapatan, WhatsApp, gambar
app/Http/Controllers/ publik, dan Admin/ untuk panel
resources/views/      site/ (halaman), components/ (komponen Blade: site/nav, site/footer, home/hero, home/showcase, page-head, section-head, accent, cta, service-list, client-strip, portfolio/event-card, portfolio/lightbox …), admin/, layouts/
public/assets/        CSS dan JS dari prototipe, tanpa build tool
database/seeders/     DatabaseSeeder (data awal), DemoSeeder (data contoh)
```

## Aturan bisnis (jangan diubah tanpa persetujuan tim)
- Layanan punya `pricing_type`: `tier` (pilih 1 paket) atau `per_unit` (harga × jumlah).
  - Sound 3/5/7/10 jt · Lighting 1,5/2,25/3 jt (**contoh, menunggu FZM**) · Multimedia 1,5/2,5/3 jt
  - Videotron indoor Rp400rb/m² · outdoor Rp650rb/m² · Rigging Rp150rb/m
- Satu booking = beberapa item; harga di-*snapshot* ke `booking_items`. **Server wajib menghitung ulang** total (JS hanya pratinjau).
- **DP = 50% total**, dibulatkan ke atas ke ribuan. PPN nonaktif default (sakelar 11% di pengaturan).
- Status booking: `pending → confirmed → completed`, `pending → rejected`, `pending|confirmed → cancelled`.
  - `confirmed` hanya jika DP 50% sudah tercatat; mengunci tanggal (1 tanggal = 1 booking confirmed, unique index + transaksi).
  - `completed` hanya jika lunas. `pending` tidak mengunci tanggal.
- Status bayar dihitung: `unpaid` → `dp` (≥ 50%) → `paid` (= total). Pembayaran tidak boleh melebihi sisa; salah input di-*void*, bukan dihapus.
- Pendapatan = jumlah pembayaran tercatat berdasarkan `paid_at` (Asia/Jakarta). Kartu "hari ini" & "bulan ini" mulai dari 0 tiap periode; data lama tetap disimpan.
- Dokumen: **invoice per booking** `INV/YYYY/MM/NNN` (jatuh tempo = tanggal acara) + **kuitansi per pembayaran** `KWT/YYYY/MM/NNN`. Format invoice mengikuti `docs/design-reference/contoh-invoice.png` (header tabel merah, tanpa branding pihak lain).
- Kode booking `FZM-YYYYMMDD-XXXX`. Nomor WA dinormalisasi ke `62…`, pesan lewat `https://wa.me/<nomor>?text=…`.

## Desain
Panduan lengkap: `docs/DESIGN.md` (kanvas: https://claude.ai/artifact/Tbtxyek7AgwfJCPEMePNxw).
- Flat color, tanpa pola. Tanpa gradasi (termasuk footer). Pengecualian: material kaca buram (navbar publik, navbar admin, menu tarik-turun, notifikasi).
- Warna: latar `--bg #FAFAFA`, teks `--ink #101010`, aksen `--red #C2272D` (hanya aksen & aksi utama), `--muted #5F5F5F`, garis `--hairline #E0E0E0`, kartu `--surface #FFF`, tanggal terisi `--taken #EAD0D2`.
- Font: **Open Sauce One** (lokal di `public/assets/fonts`, SIL OFL) untuk semua teks; **Instrument Serif** italic merah untuk kata aksen hero (`.serif`).
- Radius: tombol & navbar kapsul, kartu 24–32px, blok CTA 40px. Lebar konten 1360px (publik) / 1100px (admin). Jarak seksi 160px.
- Tidak ada label bernomor seperti "(01) Layanan".
- Publik: pil merek kiri atas, navbar kapsul bawah (Beranda, Layanan, Portofolio, Tentang, Kontak, Pesan). Logo kiri atas polos tanpa kotak dan tanpa animasi. Beranda: hero "Rangkai *Cipta,* Hidupkan *Karya.*" diketik huruf per huruf dengan kursor merah yang langsung hilang setelah selesai (`site.js`); hero setinggi layar agar tidak mepet navbar, teks bawahnya dari pengaturan `hero_title` + `hero_subtitle`, daftar layanan, strip logo klien berjalan tanpa kotak (hanya garis batas), CTA hitam. Footer: tiga kolom + wordmark "FZM PRODUCTION" Inter Tight 700 huruf rapat, flat: FZM hitam, PRODUCTION merah (`cqw`). Portofolio: kategori hanya Pernikahan (`Wedding`) & Korporat (`Corporate`), 4 kartu acara (unggulan lalu terbaru, `PortfolioController::SHOWN`): foto di atas, ringkasan gelap + "Lihat studi kasus"; klik foto membuka galeri berlatar buram yang membesar dari kartu (desktop) atau lembar bawah yang bisa ditarik (ponsel) (`portfolio.js`). Pesan: grid kartu layanan dengan paket sebagai daftar iOS, kalender & data berdampingan sama tinggi, angka tanpa animasi menghitung. Halaman klien publik dihapus (`/klien` dialihkan ke `/#klien`).
- Admin: navbar putih menempel di dasar (Dashboard, Pemesanan, Layanan, Portofolio, Klien, Pengaturan, Keluar); menu Klien mengelola logo (tambah, ganti, hapus logo, hapus klien). Bagian klien di beranda tersembunyi selama daftar kosong. Pindah menu tanpa muat ulang (`admin.js`, skrip halaman di `#pageScripts`). Grafik batang hitam, periode berjalan merah; donut hitam/merah/abu.
- **Web satu halaman (one-scroll)**: beranda memuat semua bagian — hero → `#layanan` → `#portofolio` (grid foto bento, klik foto = galeri `portfolio.js`, judul = studi kasus; `HomeController::PROJECTS`) → `#klien` → CTA → `#tentang` → `#kontak` (formulir `kontak.js`). `/layanan`, `/portofolio`, `/tentang`, `/kontak` dialihkan 301 ke bagiannya. Halaman terpisah tinggal: detail layanan, studi kasus portofolio, `/pesan`, `/cari`. Navbar menggulir ke `#bagian`; pil hitam mengikuti bagian yang terlihat (scrollspy di `site.js`).
- Halaman publik disusun dari komponen Blade (`resources/views/components`), mis. `home/hero`, `home/showcase`, `home/about`, `home/contact`, `site/nav`, `site/footer`, `wa-link` (nomor WA yang bisa diketuk).
- Merah = aksen poin penting: label `.eyebrow` bertitik merah, kata aksen `<x-accent>` (serif merah), harga, nama klien, DP/total, nomor WA, `.accent` untuk frasa kunci.
- Konten publik muncul **mengikuti posisi scroll** (bukan animasi berdurasi): `site.js` mengisi `--p` 0→1 per elemen tiap frame, `base.css` (`.scroll-reveal`) memetakannya ke pudar + naik + blur, jadi tidak tertinggal saat digulir cepat dan berbalik saat digulir ke atas. Galeri: pindah foto berupa pudar silang murni (`.pd-ghost`).
- Motion muncul yang tenang: buram → tajam sambil pudar + naik pelan (`--ease-out`), judul kata per kata dari buram (`data-words`), blur sedang (9px konten, 7px judul, 11px foto). Foto portofolio diam (tanpa zoom/paralaks); hover hanya mengangkat bingkai 6px dengan kurva lembut. Galeri foto bergaya kaca buram (glassmorphism). Tanpa kursor "Lihat", tanpa jumlah proyek klien.
- Dokumen PDF bermerek (dompdf, gaya `admin/documents/pdf/brand.blade.php`): **rekap pendapatan** `/admin/rekap?periode=YYYY-MM|YYYY` (`RecapController`, `RevenueService::recap`; tombol Unduh rekap mengikuti periode grafik dashboard) dan **invoice** (`pdf/invoice-body.blade.php`, dipakai PDF & pratinjau layar).
- Admin: daftar booking berlebar tetap saat ganti filter (`table-fixed` + colgroup, dropdown filter 200px, `scrollbar-gutter: stable`).
- Animasi: perpindahan halaman publik lewat View Transitions (lama memudar+buram, baru naik; fallback di `site.js`); pil hitam navbar meluncur ke menu tujuan sebelum pindah; konten muncul bertingkat saat di-scroll (`data-reveal` + `--d`); pudar singkat `data-reveal`; UX ala Apple/iOS: kurva pegas (`--spring`), tombol mengecil saat ditekan, sorotan meluncur di navbar. Admin: jempol hitam meluncur ke menu aktif, filter status kontrol segmen, sakelar iOS untuk centang, dialog/laci berlatar buram. Selalu hormati `prefers-reduced-motion`.
- Aksesibilitas: target sentuh ≥ 44px, label form, fokus 3px merah, kontras teks ≥ 4.5:1.

## Keamanan & perangkat
- Admin: tamu yang membuka `/admin/*` (selain `/admin/login`) mendapat halaman 404 (`errors/404.blade.php`, diatur di `bootstrap/app.php`); wajib login (`auth:admin`), CSRF di semua form, batas 5 percobaan login/menit, middleware `ProtectAdmin` (noindex, no-store, tanpa iframe; opsional `ADMIN_ALLOWED_IPS` di `.env` → IP lain dapat 404). `SecurityHeaders` untuk semua halaman.
- Ikon tab: `<x-favicon />` (`public/favicon.ico|svg`, `apple-touch-icon.png`, `site.webmanifest`).
- Responsif lewat media query; wajib tanpa geser samping di 320–1024px (pakai `minmax(0, 1fr)` untuk kolom grid).

## Konvensi kode frontend
- JS vanilla dalam IIFE, tanpa dependensi. Data dari server diletakkan di blok `<script type="application/json">` (`dashData`, `bookingData`) atau `window.FZM_CONFIG` dengan `@json`.
- `admin.js` adalah shell bersama (penjaga login, sidebar, dropdown, toast, `window.FZM`); muat sebelum skrip halaman admin.
- Placeholder data yang belum ada ditulis `[…]`. Nomor WA admin sementara `62XXXXXXXXXX`.
- Rupiah sebagai bilangan bulat; format `Rp` + `toLocaleString('id-ID')`.

## Pertanyaan terbuka (lihat PRD §16)
Hosting · kapasitas per tanggal (1 tanggal = 1 event?) · tingkat harga Lighting final · kebijakan DP jika batal · data perusahaan (alamat, NPWP, rekening) · bahasa selain Indonesia.
