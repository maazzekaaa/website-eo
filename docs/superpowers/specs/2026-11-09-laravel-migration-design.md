# Rancangan: FZM Production Mediatama versi Laravel

Tanggal: 9 November 2026. Sumber: `fzm-production.zip` (`docs/PRD.md` v0.2, `docs/ALUR-PROGRAM.md` v0.2, `frontend/`).

## Tujuan dan alasan

Mengubah prototipe statis menjadi website yang benar-benar berjalan: data tersimpan di MySQL, pemesanan masuk ke database, dan admin mengelola seluruh isi situs tanpa menyentuh kode. Proyek ini tugas Kelompok 12, jadi hasilnya harus lolos skenario uji di PRD §12 dan berjalan lokal di MacBook (Herd + DBngin + TablePlus).

## Sasaran

- Seluruh user story Sprint 1-4 di PRD terpenuhi, termasuk halaman yang belum ada di prototipe.
- Tampilan 8 halaman prototipe tidak berubah.
- Aturan bisnis ditegakkan di server: harga dihitung ulang, DP 50% sebelum konfirmasi, 0 double booking, pembayaran tidak melebihi sisa, void bukan hapus.

## Bukan sasaran

- Payment gateway, akun pelanggan, multi-admin dengan peran, chat, refund otomatis (PRD §4).
- Deploy ke hosting. Hosting masih pertanyaan terbuka di PRD.
- Bahasa selain Indonesia.

## Gagal walau secara teknis jalan, jika

- Tampilan atau animasi prototipe rusak atau terasa berbeda.
- Total dari browser bisa memengaruhi harga yang tersimpan.
- Data demo pendapatan ikut terbawa ke data produksi.

## Batasan

- macOS, PHP 8.4 dari Herd, MySQL dari DBngin (`127.0.0.1:3310`, user `root`), database `fzm_production`, dilihat lewat TablePlus.
- Bahasa kerja Indonesia: UI, komentar kode, pesan commit.
- Zona waktu aplikasi `Asia/Jakarta`.
- Uang disimpan sebagai `BIGINT` Rupiah, tanpa float.

## Pendekatan yang dipertimbangkan

| Pendekatan | Untung | Rugi |
|---|---|---|
| **Blade + CSS/JS prototipe apa adanya (dipilih)** | Tampilan terjamin sama, tanpa build tool, paling cepat | Menyimpang dari tulisan PRD §11 (Tailwind, Alpine, Chart.js) |
| Blade + Tailwind + Alpine sesuai PRD §11 | Sesuai tulisan PRD | Menulis ulang frontend yang sudah jadi, risiko tampilan berubah |
| API Laravel + frontend terpisah | Frontend dan backend lepas | Berlebihan untuk situs ini, auth dan CSRF lebih rumit |

Pilihan ini keputusan Claude, karena pengguna menyerahkan caranya. Pengguna sudah memutuskan: Laravel (bukan Django seperti di `CLAUDE.md` zip), MySQL lewat DBngin, dan cakupan lengkap sesuai PRD.

## Struktur teknis

- Laravel versi stabil terbaru di root `fzm-website/`, disajikan Herd di `http://fzm-website.test`.
- Repo git baru di `fzm-website/`, terpisah dari repo di folder home.
- Aset prototipe disalin ke `public/assets/`, tanpa Vite.
- Layout Blade: `layouts/site` (navbar, footer, tombol WA), `layouts/admin` (sidebar, topbar), `layouts/document` (cetak).
- Data ke JavaScript lewat blok JSON yang sudah disiapkan prototipe (`dashData`, `bookingData`, `FZM_CONFIG`).
- Logika demo di JS diganti: penjaga localStorage di `admin.js` dan login palsu di `login.js` dihapus, `preventDefault` submit di `pesan.js` dan `booking.js` dihapus, kalender mengambil data dari `/api/availability`.
- Grafik memakai SVG buatan `dashboard.js` yang sudah ada, bukan Chart.js.

## Data

Tabel sesuai PRD §9: `admins`, `settings`, `services`, `service_options`, `clients`, `portfolios`, `portfolio_images`, `bookings`, `booking_items`, `payments`, ditambah `document_sequences` (ALUR §9).

- `bookings.confirmed_date` kolom generated (`event_date` jika status `confirmed` atau `completed`, selain itu NULL) dengan UNIQUE index.
- Tambahan dari prototipe di luar PRD §9: `portfolios.caption` dan `portfolios.services`, `settings.instagram_url`, `tiktok_url`, dan `youtube_url`. `settings.map_embed` menyimpan URL Google Maps, bukan HTML.
- Harga disalin ke `booking_items` saat pemesanan.
- `settings` satu baris, dibaca lewat helper `setting()` dengan cache yang dibersihkan saat disimpan.

Seeder dipisah dua:
- `DatabaseSeeder`: admin, pengaturan, layanan dan harga PRD FR-3. Nilai yang belum ada tetap ditulis `[…]`, nomor WA `62XXXXXXXXXX`.
- `DemoSeeder`: portofolio, klien, booking, dan pembayaran contoh dari prototipe. Dijalankan terpisah agar mudah dibuang.

## Logika server

Service sesuai ALUR §1: `CartPricingService`, `BookingService`, `PaymentService`, `DocumentNumberService`, `RevenueService`, `WhatsAppService`. Konfirmasi dan pencatatan pembayaran berjalan dalam transaksi dengan `lockForUpdate`, sesuai pseudocode ALUR §9.

## Halaman

Rute mengikuti ALUR §10.

**Publik, dari prototipe:** `/` (`index.html`), `/pesan` dan `/pesan/sukses/{kode}` (`pesan.html`), `/kontak` (`kontak.html`).

**Publik, baru dengan gaya `site.css`:** `/layanan`, `/layanan/{slug}`, `/portofolio`, `/portofolio/{slug}`, `/klien`, `/klien/{id}`, `/tentang`, `/cari`, `/sitemap.xml`.

**Admin, dari prototipe:** `/admin/login`, `/admin`, `/admin/bookings/{id}`, invoice, kuitansi.

**Admin, baru dengan gaya `admin.css`:** daftar `/admin/bookings`, CRUD `/admin/services` (dengan tingkat harga dan badge promo), `/admin/portfolios` (unggah foto, warna folder), `/admin/clients` (logo), `/admin/settings` (konten beranda, Tentang Kami, WA, bank, NPWP, tanda tangan, PPN).

**Dokumen:** cetak lewat CSS print, PDF lewat `barryvdh/laravel-dompdf` dengan template terpisah yang kompatibel dompdf, karena dompdf tidak mendukung seluruh CSS modern.

## Keamanan

- Guard `admin` di atas tabel `admins`, sandi bcrypt, throttle login.
- CSRF, honeypot, dan rate limit pada form pemesanan.
- Bukti transfer di disk privat dan hanya bisa dibuka admin. Foto publik di disk `public`.
- Unggahan divalidasi tipe dan ukurannya, lalu dikonversi ke WebP dengan thumbnail.

## Pengujian

- Feature test dengan database MySQL terpisah `fzm_production_test`, karena kolom generated dan pengujian kunci transaksi harus berjalan di MySQL asli.
- Skenario wajib PRD §12: keranjang multi-item, manipulasi harga ditolak, konfirmasi tanpa DP ditolak, pembayaran melebihi sisa ditolak, DP lalu pelunasan, void, dua konfirmasi di tanggal yang sama, ganti periode grafik.
- Pengecekan di browser untuk alur publik dan admin, pada ukuran ponsel dan desktop.

## Keputusan Claude yang perlu dicek

1. **Satu tanggal, satu event** (pertanyaan terbuka PRD §16 no. 2), mengikuti FR-8.
2. **Booking dibatalkan setelah DP:** pembayaran tetap tercatat sebagai pendapatan dan statusnya tidak berubah. Pengembalian dana dicatat admin dengan void jika memang dikembalikan. Kebijakan FZM masih terbuka (PRD §16 no. 4).
3. **Harga Lighting** memakai angka contoh di PRD sampai ada angka final, dan bisa diubah dari admin.
4. **Akun admin lokal** `admin@fzm.id` / `admin123` dari prototipe, hanya untuk lokal dan wajib diganti sebelum online.
5. **Pencarian cepat (US-4.1)** berupa halaman `/cari` yang mencari layanan dan portofolio.
6. **Peta di halaman kontak** berupa embed dari kolom `map_embed`, kosong sampai diisi admin.
7. **Dokumen PRD, ALUR, dan FRONTEND** ikut disalin ke `docs/`, dan `CLAUDE.md` diperbarui agar menyebut Laravel, bukan Django.
8. **Booking `completed` tetap mengunci tanggal**, agar tanggal acara yang ditandai selesai lebih awal tidak terbuka lagi.
9. **Halaman sukses pemesanan hanya untuk sesi pembuatnya**, agar kode booking yang ditebak tidak membuka data pemesan lain.
10. **Void:** ditolak pada booking `completed`. Jika membuat bayaran di bawah DP pada booking `confirmed`, status tetap `confirmed` dengan peringatan untuk admin.
11. **Ganti kata sandi admin** ada di halaman pengaturan, karena akun lokal wajib diganti sebelum online.
