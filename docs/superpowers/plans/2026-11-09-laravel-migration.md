# FZM Production versi Laravel: Rencana Implementasi

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengubah prototipe statis FZM Production menjadi aplikasi Laravel dengan MySQL, lengkap sesuai PRD v0.2.

**Architecture:** Laravel MVC dengan service untuk logika bisnis (ALUR §1). Blade merender HTML prototipe apa adanya, CSS/JS prototipe tinggal di `public/assets/` tanpa build tool. Aturan uang dan jadwal ditegakkan di server dan di database.

**Tech Stack:** Laravel 13 · PHP 8.4 (Herd) · MySQL 9.7 (DBngin) · Blade · JS vanilla prototipe · `barryvdh/laravel-dompdf` · GD (WebP) · PHPUnit

**Spec:** `docs/superpowers/specs/2026-11-09-laravel-migration-design.md`. Sumber prototipe: `/Users/dhimasekasapta/Downloads/fzm-production.zip`. Dokumen acuan setelah Task 1: `docs/PRD.md`, `docs/ALUR-PROGRAM.md`.

## Global Constraints

- UI, komentar kode, dan pesan commit berbahasa Indonesia.
- Zona waktu aplikasi `Asia/Jakarta`, locale `id`.
- Uang `BIGINT` Rupiah (bilangan bulat), tanpa float.
- DP = `min(total, intdiv(total + 1999, 2000) * 1000)` (50% dibulatkan ke atas ke ribuan).
- PPN = `intdiv(subtotal * tax_rate + 50, 100)`, nonaktif default, tarif default 11.
- Status booking: `pending → confirmed → completed`, `pending → rejected`, `pending|confirmed → cancelled`.
- Status bayar: `unpaid` → `dp` (≥ DP) → `paid` (= total).
- Format: `FZM-YYYYMMDD-XXXX`, `INV/YYYY/MM/NNN`, `KWT/YYYY/MM/NNN`.
- Database `fzm_production`, database uji `fzm_production_test`, `127.0.0.1:3310`, user `root`, tanpa sandi. Klien CLI: `/Users/Shared/DBngin/mysql/9.7.2_arm64/bin/mysql`.
- URL lokal `http://fzm-website.test`.
- Tampilan 8 halaman prototipe tidak boleh berubah. Kelas CSS, `id`, dan animasi dipertahankan.
- Semua teks dari pengguna dirender dengan escape. Data ke JS lewat `@json` di blok `<script type="application/json">`.
- Commit lokal per task, tanpa push.

## Review Focus

1. Pesanan gagal validasi di server: keranjang, tanggal, dan data pemesan tetap terisi, bukan kosong. Test di Task 5.
2. Booking `completed` dengan tanggal yang belum lewat: tanggalnya tetap terkunci. Test di Task 3.
3. Void setelah konfirmasi yang membuat bayaran di bawah DP: status tetap `confirmed` dengan peringatan. Void pada booking `completed` dan pembayaran pada booking nonaktif ditolak. Test di Task 4.
4. Menebak kode booking di `/pesan/sukses/{kode}` tidak boleh menampilkan data pemesan lain. Test di Task 5.
5. Nama pemesan berisi HTML atau `</script>` tidak boleh berjalan di dashboard, detail booking, maupun dokumen. Test di Task 7.

---

## Struktur berkas

```
app/Exceptions/BookingRuleException.php, DateUnavailable.php
app/Http/Controllers/        HomeController, ServiceController, PortfolioController, ClientController,
                             PageController, SearchController, SitemapController, AvailabilityController,
                             CartController, BookingController
app/Http/Controllers/Admin/  AuthController, DashboardController, RevenueController, BookingController,
                             PaymentController, DocumentController, ServiceController, PortfolioController,
                             ClientController, SettingController
app/Models/                  Admin, Setting, Service, ServiceOption, Client, Portfolio, PortfolioImage,
                             Booking, BookingItem, Payment
app/Services/                PricedCart, CartPricingService, BookingService, PaymentService,
                             DocumentNumberService, RevenueService, WhatsAppService, ImageService
app/helpers.php              rupiah(), setting(), terbilang()
database/seeders/            DatabaseSeeder, DemoSeeder
public/assets/               css/ dan js/ dari prototipe
resources/views/layouts/     site, admin, document
resources/views/partials/    folder-card, service-card
resources/views/site/, admin/, admin/documents/, admin/documents/pdf/
tests/Unit/, tests/Feature/
```

---

### Task 1: Fondasi proyek

**Files:**
- Create: proyek Laravel di root, `.env`, `.env.example`, `phpunit.xml`, `.gitignore`, `public/assets/**`, `docs/PRD.md`, `docs/ALUR-PROGRAM.md`, `docs/FRONTEND.md`, `docs/design-reference/contoh-invoice.png`, `CLAUDE.md`, `README.md`
- Test: `tests/Feature/SmokeTest.php`

**Interfaces:**
- Produces: koneksi MySQL, situs di `http://fzm-website.test`, aset di `public/assets/`.

- [ ] **Step 1: Pastikan MySQL menyala.** Run: `/Users/Shared/DBngin/mysql/9.7.2_arm64/bin/mysql -h127.0.0.1 -uroot -e 'select version();'`. Expected: `9.7.2`. Jika gagal, berhenti dan minta pengguna menyalakan server di DBngin.
- [ ] **Step 2: Buat proyek.** Karena `docs/` sudah ada, jalankan `composer create-project laravel/laravel` ke folder scratchpad, lalu salin isinya (termasuk dotfile) ke `fzm-website/`.
- [ ] **Step 3: Jalankan `git init` di `fzm-website/`.** Run: `git rev-parse --show-toplevel`. Expected: `/Users/dhimasekasapta/fzm-website`.
- [ ] **Step 4: Atur `.env` dan konfigurasi.** `APP_NAME="FZM Production"`, `APP_URL=http://fzm-website.test`, `APP_LOCALE=id`, `DB_CONNECTION=mysql`, `DB_DATABASE=fzm_production`, zona waktu `Asia/Jakarta`. `phpunit.xml` memakai MySQL `fzm_production_test`.
- [ ] **Step 5: Buat kedua database** dengan charset `utf8mb4`.
- [ ] **Step 6: Salin aset dan dokumen dari zip.** Perbarui `CLAUDE.md`, `FRONTEND.md`, serta bagian Teknologi PRD dan ALUR agar menyebut Laravel + Blade + CSS/JS prototipe. Tulis cara menjalankan proyek di `README.md`.
- [ ] **Step 7: Pasang dompdf dan daftarkan situs.** Run: `composer require barryvdh/laravel-dompdf`, `herd link fzm-website`, `php artisan storage:link`.
- [ ] **Step 8: Tulis test.** `test_koneksi_memakai_mysql`: `DB::connection()->getDriverName() === 'mysql'`. `test_aset_prototipe_tersedia`: `public_path('assets/css/base.css')` ada.
- [ ] **Step 9: Jalankan.** Run: `php artisan test`. Expected: PASS. `curl -I http://fzm-website.test` memberi 200.
- [ ] **Step 10: Commit** `chore: inisialisasi proyek Laravel dan aset prototipe`.

### Task 2: Skema, model, helper, dan seeder

**Files:**
- Create: migration semua tabel, semua `app/Models/*`, `app/helpers.php`, `database/seeders/DatabaseSeeder.php`
- Modify: `composer.json` (autoload `files`), `config/auth.php`
- Test: `tests/Unit/HelpersTest.php`, `tests/Feature/SchemaTest.php`

**Interfaces:**
- Produces:
  - `rupiah(int $n): string`, `setting(string $key, mixed $default = null): mixed`, `terbilang(int $n): string`
  - `Setting::current(): Setting` (cache, dibersihkan saat `saved`)
  - `Service::scopeActive`, `ServiceOption::scopeActive`, `Payment::scopeValid` (`voided_at` null)
  - `Booking::paidAmount(): int`, `Booking::restAmount(): int`, `Booking::isOpen(): bool` (`pending|confirmed`), konstanta `Booking::LOCKING_STATUSES = ['confirmed','completed']`
  - Guard `admin`, provider tabel `admins`

Kolom mengikuti PRD §9, dengan tambahan:
- `settings`: `instagram_url`, `tiktok_url`, `youtube_url` (nullable). `map_embed` menyimpan URL, bukan HTML.
- `portfolios`: `caption`, `services` (teks tampilan, dibutuhkan `.folder-cap` dan `data-services`).
- `bookings.confirmed_date`: `storedAs("IF(status IN ('confirmed','completed'), event_date, NULL)")` dengan UNIQUE.
- `document_sequences(prefix, period, last_number)`, UNIQUE `(prefix, period)`.
- `payments.paid_at` bertipe `date`.
- FK: `booking_items.service_id` dan `service_option_id` `nullOnDelete`, `portfolios.client_id` `nullOnDelete`, `portfolio_images.portfolio_id` `cascadeOnDelete`, `booking_items.booking_id` dan `payments.booking_id` `restrictOnDelete`.

Slug layanan wajib `sound`, `lighting`, `multimedia`, `videotron-indoor`, `videotron-outdoor`, `rigging`, sesuai `data-service` dan `?layanan=` di prototipe.

- [ ] **Step 1: Tulis test helper.** `rupiah(5800000) === 'Rp5.800.000'`; `terbilang(5800000) === 'lima juta delapan ratus ribu'`; `terbilang(1100) === 'seribu seratus'`; `terbilang(111) === 'seratus sebelas'`; `terbilang(0) === 'nol'`.
- [ ] **Step 2: Tulis test skema.** `test_dua_booking_confirmed_di_tanggal_sama_ditolak_database`: expect `UniqueConstraintViolationException`. `test_banyak_pending_di_tanggal_sama_boleh`. `test_seeder_mengisi_harga_prd`: Sound punya opsi 3/5/7/10 jt, Lighting 1,5/2,25/3 jt, Multimedia 1,5/2,5/3 jt, Videotron Indoor 400.000 `m²`, Outdoor 650.000 `m²`, Rigging 150.000 `m`, Multimedia berbadge `baru`, `tax_enabled` false, `tax_rate` 11, `wa_number` `62XXXXXXXXXX`.
- [ ] **Step 3: Jalankan.** Run: `php artisan test`. Expected: FAIL.
- [ ] **Step 4: Implementasikan migration, model, helper, seeder.** Admin `admin@fzm.id` / `admin123`. Isi `settings` dari teks prototipe, dengan placeholder `[…]`. `hero_title` berisi `Panggung yang hidup,\nacara yang dikenang.`
- [ ] **Step 5: Jalankan.** Run: `php artisan migrate:fresh --seed && php artisan test`. Expected: PASS.
- [ ] **Step 6: Commit** `feat: skema database, model, dan seeder harga`.

### Task 3: Harga, nomor dokumen, dan aturan booking

**Files:**
- Create: `app/Services/PricedCart.php`, `CartPricingService.php`, `DocumentNumberService.php`, `BookingService.php`, `WhatsAppService.php`, `app/Exceptions/BookingRuleException.php`, `DateUnavailable.php`
- Test: `tests/Feature/CartPricingTest.php`, `DocumentNumberTest.php`, `BookingServiceTest.php`, `WhatsAppServiceTest.php`

**Interfaces:**
- Produces:
  - `CartPricingService::price(array $items, array $qty): PricedCart`. `$items` = `[slug => option_id]`, `$qty` = `[slug => int]`, sama seperti nama field `items[sound]` dan `qty[rigging]` di prototipe. Gagal: `ValidationException` pada key `items`.
  - `PricedCart` readonly: `array $lines` (`service_id`, `service_option_id`, `description`, `qty`, `unit`, `unit_price`, `subtotal`), `int $subtotal`, `int $taxRate`, `int $tax`, `int $total`, `int $dp`
  - `DocumentNumberService::next(string $prefix, ?CarbonInterface $at = null): string`
  - `BookingService::create(array $data, PricedCart $cart): Booking`, `confirm(Booking $b): void`, `reject(Booking $b, ?string $reason): void`, `cancel(Booking $b, ?string $reason): void`, `complete(Booking $b): void`
  - `BookingRuleException` (pesan untuk ditampilkan), `DateUnavailable extends BookingRuleException`
  - `WhatsAppService::normalize(string $n): string`, `bookingMessage(Booking $b): string`, `bookingUrl(Booking $b): string`

- [ ] **Step 1: Tulis test.**
  - `test_keranjang_multi_item`: Sound Paket 2 + Videotron Indoor 12 + Rigging 12 → subtotal 11.600.000, total 11.600.000, dp 5.800.000, deskripsi `Sound System – Paket 2`, unit `Paket`/`m²`/`m`.
  - `test_dp_dibulatkan_ke_atas`: total 1.500.500 → dp 751.000.
  - `test_ppn_aktif`: subtotal 3.000.000, tarif 11 → tax 330.000, total 3.330.000, dp 1.665.000.
  - `test_ditolak`: opsi milik layanan lain, opsi atau layanan nonaktif, qty 501, slug tak dikenal, tier dengan qty, keranjang kosong.
  - `test_qty_nol_diabaikan`.
  - `test_nomor_berurutan_per_bulan`: `INV/2026/11/001`, `/002`, lalu `INV/2026/12/001`. Prefix INV dan KWT punya hitungan sendiri.
  - `test_create_menyimpan_snapshot`: mengubah harga setelah booking tidak mengubah `booking_items`. Kode cocok `/^FZM-\d{8}-\d{4}$/`, status `pending`, `invoice_number` terisi.
  - `test_create_tanggal_terkunci`: `DateUnavailable`.
  - `test_konfirmasi_butuh_dp`, `test_konfirmasi_tanggal_sudah_dikunci`, `test_konfirmasi_menangkap_pelanggaran_unique` (simulasikan `UniqueConstraintViolationException`, harus jadi `DateUnavailable`).
  - `test_selesai_butuh_lunas`, `test_transisi_tidak_sah_ditolak` (contoh `rejected → confirmed`, `completed → cancelled`).
  - `test_completed_tetap_mengunci_tanggal` (Review Focus 2).
  - `test_alasan_tolak_masuk_admin_note`.
  - `test_normalize`: `0812-3456-7890` dan `+62 812 3456 7890` → `6281234567890`.
  - `test_pesan_wa`: memuat kode, item, total, DP, tanggal, lokasi, nama. URL memakai `rawurlencode`.
- [ ] **Step 2: Jalankan.** Run: `php artisan test --filter='CartPricing|DocumentNumber|BookingService|WhatsApp'`. Expected: FAIL.
- [ ] **Step 3: Implementasikan.** Gunakan transaksi dan `lockForUpdate` sesuai ALUR §9. `DocumentNumberService` mengunci baris urutan dengan `FOR UPDATE`. Kode booking memakai 4 digit acak (1000-9999), ulangi jika bentrok.
- [ ] **Step 4: Jalankan.** Expected: PASS.
- [ ] **Step 5: Commit** `feat: perhitungan harga dan aturan booking`.

### Task 4: Pembayaran dan rekap pendapatan

**Files:**
- Create: `app/Services/PaymentService.php`, `RevenueService.php`
- Test: `tests/Feature/PaymentServiceTest.php`, `RevenueServiceTest.php`

**Interfaces:**
- Consumes: `DocumentNumberService::next`, `Booking::paidAmount`
- Produces:
  - `PaymentService::record(Booking $b, array $data, int $adminId): Payment`, `$data` = `amount`, `type` (`dp|pelunasan|lainnya`), `method` (`transfer|tunai`), `paid_at`, `proof_path?`, `note?`
  - `PaymentService::void(Payment $p, string $reason): void`
  - `RevenueService::dashboardData(): array` dengan bentuk persis `dashData`: `today`, `daily['YYYY-MM']` (index 0 = tanggal 1, sampai hari ini untuk bulan berjalan), `monthly['YYYY']`, `bookings[]` (`code`, `name`, `date`, `total`, `paid`, `status`, `items`, `wa`, tambah `url`, `confirmUrl`)
  - `RevenueService::today(): int`, `month(): int`

- [ ] **Step 1: Tulis test.**
  - `test_nominal_melebihi_sisa_ditolak`, `test_nominal_nol_ditolak`.
  - `test_dp_lalu_pelunasan`: status `unpaid → dp → paid`, kuitansi `KWT/YYYY/MM/NNN` terbit.
  - `test_void_menghitung_ulang`: status turun, nominal hilang dari pendapatan, nomor kuitansi tidak dipakai ulang, `void_reason` tersimpan.
  - `test_void_alasan_wajib`, `test_void_dua_kali_ditolak`.
  - Review Focus 3: `test_void_confirmed_di_bawah_dp_tetap_confirmed`, `test_void_completed_ditolak`, `test_bayar_booking_nonaktif_ditolak` (rejected, cancelled, completed). `test_void_booking_cancelled_boleh`.
  - `test_pembayaran_booking_batal_tetap_pendapatan` (keputusan rancangan no. 2).
  - `test_kartu_hari_ini_dan_bulan_ini`: dengan `Carbon::setTestNow`, pergantian hari dan bulan mulai dari 0, data lama tetap ada di `daily`.
  - `test_bentuk_dashboard_data`: panjang `daily` bulan lalu = jumlah harinya, bulan berjalan = tanggal hari ini.
- [ ] **Step 2: Jalankan.** Run: `php artisan test --filter='PaymentService|RevenueService'`. Expected: FAIL.
- [ ] **Step 3: Implementasikan** sesuai ALUR §9.
- [ ] **Step 4: Jalankan.** Expected: PASS.
- [ ] **Step 5: Commit** `feat: pencatatan pembayaran dan rekap pendapatan`.

### Task 5: Halaman publik dari prototipe dan alur pemesanan

**Files:**
- Create: `resources/views/layouts/site.blade.php`, `partials/folder-card.blade.php`, `site/home.blade.php`, `site/pesan.blade.php`, `site/pesan-sukses.blade.php`, `site/kontak.blade.php`, controller Home, Page, Booking, Availability, Cart, `app/Http/Requests/StoreBookingRequest.php`, `routes/web.php`
- Modify: `public/assets/js/pesan.js`, `public/assets/js/kontak.js`
- Test: `tests/Feature/PublicPagesTest.php`, `tests/Feature/BookingFlowTest.php`

**Interfaces:**
- Consumes: `CartPricingService::price`, `BookingService::create`, `WhatsAppService::bookingUrl`, `setting()`
- Produces: route bernama `home`, `layanan.index`, `layanan.show`, `portofolio.index`, `portofolio.show`, `klien.index`, `klien.show`, `tentang`, `kontak`, `pesan.create`, `pesan.store`, `pesan.success`, `cari`, plus `@yield('title')`, `@yield('description')`, `@section('content')` pada `layouts/site`.

Perubahan JS:
- `pesan.js`: submit dikirim ke server setelah validasi klien lolos, kalender mengambil `/api/availability?month=` saat ganti bulan, total pratinjau memakai `cfg.taxRate`, status awal dipulihkan dari `cfg.old`. Blok pembuatan kode dan pesan WA di browser dihapus.
- `kontak.js`: nomor dari `cfg.adminWa`, perilaku lain tetap.

`FZM_CONFIG` di `pesan`: `adminWa`, `today`, `locked`, `taxRate`, `availabilityUrl`, `old`.

- [ ] **Step 1: Tulis test.**
  - `test_halaman_publik_200`: `/`, `/pesan`, `/kontak`.
  - `test_beranda_dari_database`: harga layanan, folder `folder--{warna}`, nama klien, judul hero tampil.
  - `test_pesan_sukses_tersimpan_dan_ke_halaman_sukses`: booking `pending`, item tersimpan, redirect ke `pesan.success`, ada tautan `https://wa.me/`.
  - `test_harga_dari_browser_diabaikan`: mengirim `total=1000` dan `price=1` tidak mengubah total.
  - `test_tanggal_terkunci_ditolak`, `test_tanggal_lampau_ditolak`, `test_honeypot_tidak_menyimpan`, `test_wa_tidak_valid_ditolak`.
  - `test_throttle_pesan`: permintaan ke-11 dalam semenit → 429.
  - `test_availability`: mengembalikan `locked` untuk tanggal `confirmed` dan `completed` saja, tanpa data pemesan. `month=abc` → 422.
  - `test_quote_menghitung_dari_database`.
  - Review Focus 1: `test_gagal_validasi_input_tetap_ada`: `FZM_CONFIG.old` memuat pilihan keranjang dan tanggal, input nama terisi.
  - Review Focus 4: `test_sukses_butuh_sesi_pembuat`: kode benar tanpa sesi pembuat → 404.
- [ ] **Step 2: Jalankan.** Run: `php artisan test --filter='PublicPages|BookingFlow'`. Expected: FAIL.
- [ ] **Step 3: Pindahkan HTML ke Blade.** Pertahankan markup prototipe. Hapus komentar "Django" dan label "data contoh". Nilai placeholder diambil dari `setting()`. Tautan sosial disembunyikan jika kosong. Baris terakhir judul hero: kata terakhir diberi `.text-gradient`.
- [ ] **Step 4: Implementasikan controller dan request.** Rute `throttle:10,1` pada `POST /pesan` dan `POST /api/cart/quote`. Sesi `booking_codes[]` dipakai untuk halaman sukses. `map_embed` dirender sebagai iframe hanya jika terisi, jika kosong tampil placeholder prototipe.
- [ ] **Step 5: Jalankan.** Expected: PASS.
- [ ] **Step 6: Cek di browser.** `http://fzm-website.test/` dan `/pesan` tampil sama dengan prototipe, pesanan contoh sampai halaman sukses, tanpa error di console.
- [ ] **Step 7: Commit** `feat: halaman publik dan alur pemesanan`.

### Task 6: Halaman publik baru, pencarian, dan sitemap

**Files:**
- Create: controller Service, Portfolio, Client, Search, Sitemap, `site/layanan/*`, `site/portofolio/*`, `site/klien/*`, `site/tentang.blade.php`, `site/cari.blade.php`, `resources/views/sitemap.blade.php`, `partials/service-card.blade.php`
- Modify: `resources/views/layouts/site.blade.php`, `public/assets/css/site.css` (penambahan di akhir berkas saja)
- Test: `tests/Feature/CatalogPagesTest.php`

**Interfaces:**
- Consumes: route bernama dari Task 5, `partials/folder-card`

Filter dan lightbox `/portofolio` memakai `site.js` yang sudah ada. `/layanan/{slug}` punya tombol "Tanya layanan ini" (WA) dan "Tambah ke pesanan" (`/pesan?layanan={slug}`). Navbar mengarah ke halaman baru. Beranda hanya menampilkan portofolio `is_featured`, dengan tautan "Lihat semua".

- [ ] **Step 1: Tulis test.**
  - `test_halaman_katalog_200`: `/layanan`, `/layanan/sound`, `/portofolio`, `/portofolio/{slug}`, `/klien`, `/klien/{id}`, `/tentang`.
  - `test_layanan_nonaktif_404_dan_tidak_terdaftar`.
  - `test_badge_promo_tampil`.
  - `test_klien_menampilkan_proyeknya`.
  - `test_cari_menemukan_layanan_dan_portofolio`, `test_cari_kosong_tidak_error`, `test_cari_tidak_menampilkan_booking`.
  - `test_sitemap_xml`: content-type XML, memuat `/layanan/sound`, tidak memuat `/admin`.
  - `test_meta_per_halaman`: `<title>` dan `og:title` berbeda antarhalaman.
- [ ] **Step 2: Jalankan.** Run: `php artisan test --filter=CatalogPages`. Expected: FAIL.
- [ ] **Step 3: Implementasikan** dengan komponen dan kelas `site.css` yang sudah ada.
- [ ] **Step 4: Jalankan.** Expected: PASS.
- [ ] **Step 5: Commit** `feat: halaman layanan, portofolio, klien, tentang, dan pencarian`.

### Task 7: Login admin, dashboard, detail booking, dan pembayaran

**Files:**
- Create: `layouts/admin.blade.php`, `admin/login.blade.php`, `admin/dashboard.blade.php`, `admin/bookings/index.blade.php`, `admin/bookings/show.blade.php`, controller `Admin/Auth`, `Dashboard`, `Revenue`, `Booking`, `Payment`, `app/Services/ImageService.php`
- Modify: `bootstrap/app.php`, `routes/web.php`, `public/assets/js/admin.js`, `login.js`, `dashboard.js`, `booking.js`
- Test: `tests/Feature/AdminAuthTest.php`, `AdminBookingTest.php`, `ImageServiceTest.php`

**Interfaces:**
- Consumes: `BookingService`, `PaymentService`, `RevenueService::dashboardData`
- Produces:
  - Route `admin.login`, `admin.logout`, `admin.dashboard`, `admin.revenue`, `admin.bookings.index`, `admin.bookings.show`, `admin.bookings.confirm|reject|cancel|complete`, `admin.payments.store`, `admin.payments.void`, `admin.payments.proof`, `admin.bookings.invoice`, `admin.payments.receipt`
  - `ImageService::store(UploadedFile $f, string $dir, string $disk = 'public'): string` (WebP, lebar maks. 1920, thumbnail 480 `{nama}_thumb.webp`), `delete(string $path, string $disk = 'public'): void`, `thumbUrl(string $path): string`
  - `layouts/admin` menerima `@section('nav', 'dashboard|bookings|services|portfolios|clients|settings')`

Perubahan JS:
- `admin.js`: hapus penjaga localStorage. `data-logout` mengirim form POST. Tautan sidebar memakai route.
- `login.js`: hapus akun demo dan simulasi. Validasi klien, tampil/sembunyi sandi, dan getar tetap ada. Submit dikirim ke server.
- `dashboard.js`: tombol konfirmasi di drawer mengirim POST ke `b.confirmUrl`, tautan detail memakai `b.url`, tautan sidebar tidak lagi `booking.html`. Hitungan badge diambil dari server.
- `booking.js`: form bayar dikirim `multipart` ke server. Aksi status dikirim lewat form tersembunyi, alasan masuk `admin_note`. Riwayat dirender dari data server, dengan tombol kuitansi dan void (dialog alasan). `nextReceipt` dihapus.
- Hasil aksi ditampilkan lewat `session('toast')` ke `FZM.toast`.
- Lonceng notifikasi menampilkan booking `pending` terbaru dan booking `pending` yang DP-nya sudah masuk.

- [ ] **Step 1: Tulis test.**
  - `test_tamu_dialihkan_ke_login` untuk semua route admin.
  - `test_login_benar`, `test_login_salah_pesan_umum`, `test_login_dibatasi` (percobaan ke-6 ditolak), `test_logout_harus_post`.
  - `test_sandi_tersimpan_bcrypt`.
  - `test_dashboard_data_json_valid`.
  - `test_filter_daftar_booking`: `status`, `payment_status`, dan pencarian `q`.
  - `test_aksi_status_lewat_http`: konfirmasi tanpa DP → pesan error, status tidak berubah.
  - `test_catat_pembayaran_lewat_http`: `amount=5.800.000` terbaca 5800000, kuitansi terbit, status bayar berubah.
  - `test_bukti_privat`: file di disk `local`, tamu dialihkan, admin 200.
  - `test_bukti_bukan_gambar_ditolak`, `test_bukti_lebih_2mb_ditolak`.
  - `test_image_service_menghasilkan_webp_dan_thumbnail`.
  - Review Focus 5: `test_nama_berbahaya_di_escape`: nama `</script><script>alert(1)</script>` tidak muncul mentah di respons dashboard dan detail booking. Periksa juga `dashboard.js` dan `booking.js`: semua data pengguna yang masuk `innerHTML` harus lewat `esc()`.
- [ ] **Step 2: Jalankan.** Run: `php artisan test --filter='AdminAuth|AdminBooking|ImageService'`. Expected: FAIL.
- [ ] **Step 3: Implementasikan.**
- [ ] **Step 4: Jalankan.** Expected: PASS.
- [ ] **Step 5: Cek di browser.** Login, catat DP, konfirmasi, lunasi, selesai, void. Tidak ada error di console.
- [ ] **Step 6: Commit** `feat: login admin, dashboard, dan pengelolaan booking`.

### Task 8: Invoice dan kuitansi

**Files:**
- Create: `layouts/document.blade.php`, `admin/documents/invoice.blade.php`, `receipt.blade.php`, `admin/documents/pdf/invoice.blade.php`, `pdf/receipt.blade.php`, `app/Http/Controllers/Admin/DocumentController.php`
- Modify: `public/assets/js/document.js`
- Test: `tests/Feature/DocumentTest.php`

**Interfaces:**
- Consumes: route `admin.bookings.invoice`, `admin.payments.receipt` dari Task 7; `terbilang()`, `rupiah()`, `setting()`

Template PDF memakai tabel dan font DejaVu Sans agar `m²` dan `–` tampil benar. Invoice A4, kuitansi A5 lanskap. Header tabel merah mengikuti `docs/design-reference/contoh-invoice.png`. Cap: `DP DIBAYAR` atau `LUNAS` (`stamp--paid`), tanpa cap jika belum bayar. Terbilang dirender di server, `data-amount` dihapus.

- [ ] **Step 1: Tulis test.**
  - `test_invoice_isi_lengkap`: nomor, jatuh tempo = tanggal acara, item, total, DP, dibayar, sisa, nomor kuitansi, bank.
  - `test_ppn_tampil_hanya_jika_ada`.
  - `test_invoice_mengabaikan_pembayaran_void`.
  - `test_kuitansi_terbilang`: 5.800.000 → `Lima juta delapan ratus ribu rupiah`, sisa setelah pembayaran ini benar walau ada pembayaran sesudahnya.
  - `test_kuitansi_void_ditandai`.
  - `test_pdf`: `?pdf=1` → `application/pdf`, isi diawali `%PDF`, nama file aman (tanpa `/`).
  - `test_dokumen_butuh_login`.
- [ ] **Step 2: Jalankan.** Run: `php artisan test --filter=DocumentTest`. Expected: FAIL.
- [ ] **Step 3: Implementasikan.** Tombol "Unduh PDF" di detail booking mengarah ke `?pdf=1`.
- [ ] **Step 4: Jalankan.** Expected: PASS.
- [ ] **Step 5: Cek visual.** Pratinjau cetak A4 dan satu PDF hasil unduhan dibuka, tata letak tidak terpotong.
- [ ] **Step 6: Commit** `feat: invoice dan kuitansi cetak serta PDF`.

### Task 9: Pengelolaan layanan, portofolio, klien, dan pengaturan

**Files:**
- Create: controller `Admin/Service`, `Portfolio`, `Client`, `Setting`, view `admin/services/*`, `admin/portfolios/*`, `admin/clients/*`, `admin/settings.blade.php`
- Modify: `routes/web.php`, `public/assets/css/admin.css` (penambahan di akhir berkas saja), `layouts/admin.blade.php`
- Test: `tests/Feature/AdminCatalogTest.php`, `AdminSettingsTest.php`

**Interfaces:**
- Consumes: `ImageService`, `Setting::current`, `layouts/admin`
- Produces: route resource `admin.services.*`, `admin.portfolios.*`, `admin.clients.*`, plus `admin.settings.edit|update`, `admin.password.update`, `admin.portfolios.images.destroy`

- [ ] **Step 1: Tulis test.**
  - `test_crud_layanan_tier_dan_per_unit`: tier wajib punya opsi, per_unit wajib `unit_label` dan `unit_price` > 0.
  - `test_ubah_harga_tidak_mengubah_booking_lama` (FR-4).
  - `test_nonaktif_hilang_dari_pesan`.
  - `test_hapus_layanan_dengan_booking`: item booking tetap ada dengan snapshot utuh.
  - `test_crud_portofolio_foto_jadi_webp`, `test_warna_folder_hanya_palet`: `red|black|maroon|silver`, `test_slug_unik`.
  - `test_hapus_portofolio_menghapus_file`.
  - `test_unggah_bukan_gambar_ditolak`, `test_unggah_svg_ditolak`.
  - `test_crud_klien_logo`, `test_hapus_klien_portofolio_tetap_ada`.
  - `test_pengaturan_tersimpan_dan_tampil_di_situs`: cache dibersihkan, hero dan WA berubah di beranda.
  - `test_ppn_aktif_berlaku_untuk_booking_baru_saja`.
  - `test_map_embed_hanya_url_google_maps`: `https://www.google.com/maps/embed?...` diterima, `javascript:` dan domain lain ditolak.
  - `test_nomor_wa_dinormalisasi`.
  - `test_ganti_sandi`: sandi lama salah ditolak.
  - `test_semua_butuh_login`.
- [ ] **Step 2: Jalankan.** Run: `php artisan test --filter='AdminCatalog|AdminSettings'`. Expected: FAIL.
- [ ] **Step 3: Implementasikan.**
- [ ] **Step 4: Jalankan.** Expected: PASS.
- [ ] **Step 5: Commit** `feat: pengelolaan layanan, portofolio, klien, dan pengaturan`.

### Task 10: Data demo dan pemeriksaan akhir

**Files:**
- Create: `database/seeders/DemoSeeder.php`
- Modify: `README.md`, `CLAUDE.md`
- Test: `tests/Feature/DemoSeederTest.php`

**Interfaces:**
- Consumes: `CartPricingService`, `BookingService`, `PaymentService`, `ImageService`

Isi `DemoSeeder`: 8 portofolio dari `index.html`, 6 klien, foto placeholder gradasi buatan GD (3-6 per folder), 7 booking dari `dashData`, dan pembayaran tersebar di bulan berjalan dan bulan-bulan sebelumnya. Booking dibuat lewat service agar lolos semua aturan, dengan tanggal relatif terhadap hari ini.

- [ ] **Step 1: Tulis test.** `test_demo_seeder_berjalan_dan_konsisten`: untuk setiap booking, `payment_status` cocok dengan total pembayaran valid, dan tidak ada dua booking mengunci tanggal yang sama. `test_database_seeder_tanpa_data_demo`: `Booking::count() === 0` setelah `DatabaseSeeder` saja.
- [ ] **Step 2: Jalankan, implementasikan, jalankan lagi.** Run: `php artisan test --filter=DemoSeeder`. Expected: FAIL lalu PASS.
- [ ] **Step 3: Isi database lokal.** Run: `php artisan migrate:fresh --seed && php artisan db:seed --class=DemoSeeder`. Periksa tabelnya.
- [ ] **Step 4: Jalankan semua test.** Run: `php artisan test`. Expected: seluruhnya PASS.
- [ ] **Step 5: Uji dua konfirmasi bersamaan.** Skrip di scratchpad menjalankan dua proses `php artisan tinker` paralel yang mengonfirmasi dua booking di tanggal sama. Expected: tepat satu berhasil.
- [ ] **Step 6: Cek di browser, ponsel dan desktop.** Beranda, folder dan lightbox, `/pesan` sampai sukses, dashboard dan ganti periode grafik, detail booking, invoice, halaman kelola. Tidak ada error di console. Bandingkan beranda dan dashboard dengan prototipe.
- [ ] **Step 7: Lengkapi `README.md`.** Cara menjalankan, cara menghubungkan TablePlus, cara menghapus data demo, dan hal yang wajib diganti sebelum online.
- [ ] **Step 8: Commit** `feat: data demo dan dokumentasi menjalankan proyek`.
