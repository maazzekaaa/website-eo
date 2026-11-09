# PRD — Website Event Organizer FZM Production Mediatama

Versi: 0.2 (draft) · Tanggal: 9 Nov 2026 · Tim: Kelompok 12 (Gigih, Dhimas)
Sumber: Proposal "Pengembangan Website Event Organizer sebagai Sistem Portofolio dan Pemesanan".

> **Perubahan dari v0.1**
> - "Paket tunggal" diganti **keranjang layanan**: satu booking bisa berisi beberapa layanan; harga per tingkat atau per meter; total dihitung otomatis.
> - **DP 50%** dari total. Tanggal baru dikunci (`confirmed`) setelah DP tercatat.
> - **Pencatatan pembayaran** oleh admin + **invoice per booking** dan **kuitansi per pembayaran** (cetak / PDF).
> - **Grafik pendapatan** harian & bulanan di dashboard admin (berdasarkan pembayaran tercatat).
> - Arah desain: putih–hitam–merah, navbar melayang transparan, portofolio berbentuk **folder ala macOS/iOS**.
> - Admin dapat mengedit seluruh konten website (termasuk teks beranda dan pengaturan perusahaan).

## 1. Ringkasan
Website resmi FZM Production Mediatama yang menampilkan portofolio (event, klien/mitra), katalog layanan beserta harga, dan pemesanan tanpa login. Pelanggan memilih beberapa layanan sekaligus seperti keranjang belanja, memilih tanggal yang tersedia, lalu diarahkan ke WhatsApp pengelola. Admin mengelola konten, mencatat pembayaran (DP/pelunasan), mengonfirmasi jadwal, mencetak invoice & kuitansi, serta memantau pendapatan.

## 2. Masalah & Tujuan
**Masalah:** portofolio hanya di Instagram (tidak terstruktur), info layanan/harga tidak terpusat, pemesanan manual via WhatsApp lambat dan berisiko bentrok jadwal, pencatatan DP/pelunasan dan bukti pembayaran belum rapi.

**Tujuan:**
- G1. Portofolio terstruktur, terfilter per kategori, dan menarik secara visual.
- G2. Layanan & harga transparan; pelanggan bisa menghitung sendiri estimasi biaya.
- G3. Pemesanan mandiri tanpa akun; tanggal `confirmed` tidak bisa dipesan lagi.
- G4. Admin mengelola seluruh konten, pesanan, dan pembayaran secara mandiri.
- G5. Setiap pembayaran tervalidasi dengan dokumen resmi (invoice & kuitansi) dan terekap sebagai pendapatan.

**Metrik keberhasilan:** 0 double booking pada pengujian; total keranjang, DP, dan sisa tagihan selalu cocok dengan perhitungan manual; seluruh skenario black-box (Tabel 3.3 proposal + skenario pembayaran) lolos; skor SUS ≥ 70 (target 81); halaman publik tampil benar di mobile dan desktop; invoice tercetak rapi di A4.

## 3. Persona
| Persona | Kebutuhan |
|---|---|
| Calon pelanggan (individu/instansi/korporat) | Melihat bukti karya, menyusun kebutuhan layanan & melihat total, cek tanggal, memesan cepat via ponsel |
| Admin/pemilik FZM | Update layanan/harga/portofolio/konten, melihat pesanan, mencatat DP & pelunasan, konfirmasi/tolak, mencetak invoice & kuitansi, memantau pendapatan |

## 4. Ruang Lingkup
**Termasuk:** halaman publik, katalog layanan & harga, portofolio (folder) & klien/mitra, kalender ketersediaan, form pemesanan dengan keranjang → WhatsApp (pre-filled), dashboard admin (login), pencatatan pembayaran, invoice & kuitansi (cetak/PDF), grafik pendapatan, promo/badge, responsif, pencarian cepat.

**Di luar lingkup:** payment gateway (pembayaran tetap manual: transfer/tunai, dicatat admin), akun pelanggan, multi-vendor/multi-admin, chat real-time, pengembalian dana (refund) otomatis.

## 5. Arah Desain (UI/UX)
- **Gaya:** simpel, modern, interaktif. Palet: putih (latar), hitam (teks/aksen gelap), merah (aksen utama, CTA, status penting). Tipografi sans-serif tegas; banyak ruang kosong.
- **Navbar:** melayang di atas (floating, sudut membulat), latar transparan dengan efek blur kaca; menjadi sedikit lebih pekat saat halaman di-scroll. Di mobile berubah jadi tombol menu.
- **Portofolio sebagai folder:** setiap event ditampilkan sebagai ikon folder bergaya macOS/iOS dengan warna yang ditentukan (default per kategori, bisa diubah admin dari palet preset).
  - *Hover:* folder sedikit terangkat (naik + bayangan), cuplikan 2–3 foto "mengintip" dari dalam folder.
  - *Klik:* folder terbuka menjadi galeri (lightbox) berisi foto hasil acara, judul, tanggal, klien, dan cerita singkat; navigasi panah/geser dan tombol tutup (Esc).
  - Di perangkat sentuh: ketuk sekali langsung membuka galeri.
- **Interaksi lain:** animasi muncul saat scroll yang halus, kartu layanan dengan hover, ringkasan keranjang lengket (desktop) / bar bawah (mobile). Hormati `prefers-reduced-motion`.
- **Dashboard admin:** latar terang, sidebar hitam, aksen merah; kartu statistik + grafik pendapatan.

## 6. Sprint & User Story
**Sprint 1 — Core Pages**
- US-1.1 Sebagai pengunjung, saya melihat beranda (hero, sorotan layanan, cuplikan folder portofolio, klien, CTA "Pesan Sekarang").
- US-1.2 Saya melihat daftar layanan, detail, dan harganya (per tingkat atau per meter).
- US-1.3 Saya melihat badge promo/baru pada layanan.

**Sprint 2 — Portofolio & Profil**
- US-2.1 Saya memfilter folder portofolio per kategori (Semua, Wedding, Corporate, Concert, Photoshoot).
- US-2.2 Saya mengarahkan kursor ke folder (terangkat) dan mengkliknya untuk membuka galeri foto acara.
- US-2.3 Saya melihat daftar klien/mitra beserta proyeknya.
- US-2.4 Saya membaca Tentang Kami (profil, pengalaman, tim).

**Sprint 3 — Pemesanan & WhatsApp**
- US-3.1 Saya memilih beberapa layanan sekaligus: memilih tingkat (Sound, Lighting, Multimedia) atau mengisi jumlah meter (Videotron, Rigging); subtotal, total, dan DP 50% dihitung otomatis.
- US-3.2 Saya mengecek ketersediaan tanggal di kalender interaktif.
- US-3.3 Saya mengisi data (nama, no. WA, tanggal, jam, lokasi, catatan) tanpa login.
- US-3.4 Jika tanggal sudah `confirmed`, sistem menolak dan menyarankan tanggal lain.
- US-3.5 Setelah valid, booking tersimpan `pending` dengan kode unik dan saya diarahkan ke WhatsApp dengan pesan berisi rincian item, total, dan DP.
- US-3.6 Tombol WhatsApp melayang dan tombol "Tanya layanan ini" di halaman layanan.
- US-3.7 Saya melihat alamat, peta, jam operasional.

**Sprint 4 — Admin, Pembayaran, Promo, Responsif**
- US-4.1 Pencarian cepat dan menu navigasi responsif.
- US-4.2 Admin login (email + password) ke `/admin`.
- US-4.3 Admin CRUD layanan & tingkat harga, promo; CRUD portofolio (foto, kategori, warna folder), klien (logo); edit profil & konten beranda; pengaturan (WA, bank, NPWP, PPN).
- US-4.4 Admin melihat tabel booking, memfilter status booking & status bayar, menolak/membatalkan.
- US-4.5 Admin mencatat pembayaran (DP/pelunasan) dengan bukti transfer; kuitansi terbit otomatis.
- US-4.6 Admin mengonfirmasi booking (mengunci tanggal) setelah DP 50% tercatat.
- US-4.7 Admin mencetak/mengunduh invoice per booking dan kuitansi per pembayaran.
- US-4.8 Admin melihat statistik & grafik pendapatan harian (bulan berjalan) dan bulanan (tahun berjalan), plus kartu "Hari ini" dan "Bulan ini".

## 7. Kebutuhan Fungsional & Aturan Bisnis
**Layanan & harga**
- FR-1 Halaman publik dapat diakses tanpa login.
- FR-2 Setiap layanan punya `pricing_type`:
  - `tier` — pelanggan memilih satu tingkat harga (mis. Sound 3jt/5jt/7jt/10jt).
  - `per_unit` — harga × jumlah unit yang diisi pelanggan (mis. Videotron indoor Rp400.000/m²).
- FR-3 Data harga awal (seeder):

| Layanan | Tipe | Harga |
|---|---|---|
| Sound System | tier | 3.000.000 / 5.000.000 / 7.000.000 / 10.000.000 |
| Lighting | tier | 1.500.000 / 2.250.000 / 3.000.000 *(contoh — tingkat final menunggu FZM)* |
| Multimedia Broadcasting System | tier | 1.500.000 / 2.500.000 / 3.000.000 |
| Videotron Indoor | per_unit (m²) | 400.000 |
| Videotron Outdoor | per_unit (m²) | 650.000 |
| Rigging | per_unit (m) | 150.000 |

- FR-4 Admin dapat mengubah/menonaktifkan layanan & tingkat harga tanpa memengaruhi booking lama (harga di-*snapshot* ke `booking_items`).

**Booking**
- FR-5 Satu booking berisi ≥ 1 item; maksimal satu tingkat per layanan `tier`; jumlah unit `per_unit` bilangan bulat ≥ 1 (batas atas wajar, mis. 500).
- FR-6 Total dihitung ulang di server (harga dari DB, bukan dari browser). `subtotal = Σ item`; `pajak = subtotal × tarif` jika PPN aktif; `total = subtotal + pajak`; `dp_wajib = 50% × total` (dibulatkan ke atas ke ribuan).
- FR-7 Status booking: `pending → confirmed → completed`; `pending → rejected`; `pending | confirmed → cancelled`.
- FR-8 **Aturan jadwal:** satu tanggal hanya boleh memiliki satu booking `confirmed`. Booking `pending` tidak mengunci tanggal (boleh beberapa pending; admin memilih satu).
- FR-9 Validasi server-side saat submit dan saat konfirmasi admin (cek ulang dalam transaksi DB + unique index pada tanggal untuk status `confirmed`).
- FR-10 Kode booking unik (`FZM-20261109-XXXX`).
- FR-11 Format nomor WA dinormalisasi ke `62…`; URL `https://wa.me/<nomor>?text=<pesan ter-encode>`; pesan memuat kode, daftar item, total, DP, tanggal, lokasi, nama.

**Pembayaran, invoice, kuitansi**
- FR-12 Admin mencatat pembayaran: nominal, jenis (`dp` / `pelunasan` / `lainnya`), metode (`transfer` / `tunai`), tanggal bayar, bukti (gambar, opsional), catatan.
- FR-13 Status bayar dihitung dari total pembayaran: `unpaid` (0) → `dp` (≥ DP wajib, < total) → `paid` (= total). Pembayaran tidak boleh melebihi sisa tagihan.
- FR-14 **Konfirmasi butuh DP:** booking hanya bisa `confirmed` jika total dibayar ≥ DP wajib (50%).
- FR-15 `completed` hanya bisa jika status bayar `paid`.
- FR-16 **Invoice per booking** — nomor `INV/YYYY/MM/NNN`, terbit saat booking dibuat, selalu menampilkan kondisi terbaru. Isi (mengikuti format referensi): kop perusahaan (nama, alamat, email, no. WA, NPWP jika ada), nomor invoice, tanggal terbit, **jatuh tempo = tanggal acara**, status, data acara (tanggal & lokasi), "Ditagihkan Kepada", tabel item (Deskripsi, Qty, Satuan, Harga Satuan, Subtotal) dengan header merah, Subtotal, PPN (jika aktif), Total Tagihan, DP 50%, Sudah Dibayar, Sisa Tagihan, riwayat pembayaran (nomor kuitansi), kotak Informasi Pembayaran (bank, no. rekening, a.n., instruksi cantumkan nomor invoice), cap status (DP/LUNAS), tanda tangan admin.
- FR-17 **Kuitansi per pembayaran** — nomor `KWT/YYYY/MM/NNN`, terbit otomatis saat pembayaran dicatat. Isi: kop, nomor kuitansi, tanggal, telah diterima dari, nominal (angka + terbilang), untuk pembayaran (jenis + nomor invoice + kode booking), metode, sisa tagihan setelah pembayaran ini, tanda tangan admin.
- FR-18 Invoice & kuitansi dapat dicetak dari browser (layout A4 via CSS print) dan diunduh sebagai PDF.
- FR-19 Pembayaran yang salah input dapat dibatalkan (void) dengan alasan; nomor kuitansi tidak dipakai ulang; pendapatan & status bayar dihitung ulang.

**Pendapatan**
- FR-20 Pendapatan = jumlah pembayaran tercatat (tidak void) berdasarkan `paid_at`.
- FR-21 Dashboard: kartu "Pendapatan hari ini" dan "Pendapatan bulan ini" (otomatis mulai dari 0 saat hari/bulan berganti, zona waktu Asia/Jakarta); grafik batang harian untuk bulan berjalan dan grafik bulanan untuk tahun berjalan; pemilih periode untuk melihat riwayat. Data lama tidak dihapus.

**Konten & pengaturan**
- FR-22 Admin dapat mengubah konten beranda (judul hero, subjudul, gambar), Tentang Kami, kontak, jam operasional.
- FR-23 Pengaturan: nomor WA admin, data bank, NPWP, alamat, tanda tangan (gambar), **PPN aktif/nonaktif (default nonaktif) + tarif (default 11%)**.
- FR-24 Upload gambar divalidasi (tipe, ukuran) dan dioptimalkan (WebP, thumbnail).

## 8. Kebutuhan Non-Fungsional
- Responsif (mobile-first); LCP < 2,5 s pada koneksi 4G untuk beranda (gambar lazy-load, WebP).
- Animasi ringan (CSS transform/opacity), tetap 60 fps di ponsel menengah; mendukung `prefers-reduced-motion`.
- Keamanan: middleware auth untuk `/admin`, bcrypt, CSRF, validasi input, rate limit + honeypot pada form booking, proteksi upload file; invoice/kuitansi hanya dapat diakses admin.
- Akurasi uang: simpan nominal sebagai bilangan bulat Rupiah (`BIGINT`), tanpa float.
- Aksesibilitas dasar: kontras cukup (merah di atas putih ≥ 4.5:1 untuk teks), label form, navigasi keyboard, fokus terlihat.
- SEO dasar: title/meta/OG per halaman, sitemap.
- Backup database berkala.

## 9. Model Data
| Tabel | Kolom utama |
|---|---|
| `admins` | id, name, email, password |
| `settings` *(menggantikan/memperluas `profiles`)* | company_name, address, email, wa_number, npwp, bank_name, bank_account, bank_holder, signature_path, tax_enabled, tax_rate, hero_title, hero_subtitle, hero_image, about, opening_hours, map_embed |
| `services` | id, name, slug, description, image, category, pricing_type (`tier`/`per_unit`), unit_label (`m²`, `m`), unit_price (untuk per_unit), badge (`promo`/`baru`/null), is_active, sort_order |
| `service_options` | id, service_id, name, price, description, is_active, sort_order |
| `clients` ("mitra") | id, name, logo, description, website |
| `portfolios` | id, client_id, title, slug, category, event_date, location, story, cover_image, folder_color, is_featured |
| `portfolio_images` | id, portfolio_id, path, caption, sort_order |
| `bookings` | id, booking_code, invoice_number, customer_name, customer_wa, event_date, event_time, event_location, note, subtotal, tax_rate, tax_amount, total, dp_amount, status, payment_status, confirmed_date *(generated, UNIQUE)*, contacted_at, admin_note, timestamps |
| `booking_items` | id, booking_id, service_id, service_option_id (nullable), description *(snapshot)*, qty, unit *(snapshot)*, unit_price *(snapshot)*, subtotal |
| `payments` | id, booking_id, receipt_number, amount, type, method, paid_at, proof_path, note, admin_id, voided_at, void_reason |

Catatan: tabel `packages` dari ERD v0.1 digantikan oleh `services` + `service_options`. "Mitra" = `clients`.

## 10. Sitemap
`/` Beranda · `/layanan` (+ `/layanan/{slug}`) — daftar layanan & harga · `/portofolio` (+ detail) · `/klien` · `/tentang` · `/pesan` (keranjang + kalender + form) · `/kontak`
Admin: `/admin/login` · `/admin` (dashboard) · `/admin/bookings` (+ detail, pembayaran, invoice, kuitansi) · `/admin/services` · `/admin/portfolios` · `/admin/clients` · `/admin/settings`

## 11. Teknologi
Laravel (MVC) · MySQL · Blade + Tailwind CSS + Alpine.js (interaksi keranjang, folder, lightbox) · Chart.js (grafik pendapatan) · barryvdh/laravel-dompdf (PDF invoice & kuitansi) · Figma/Draw.io.
**Hosting: perlu diputuskan** — Vercel tidak mendukung PHP native. Opsi: hosting PHP (cPanel), VPS, atau container (Railway/Render/Laravel Cloud).

## 12. Pengujian & Kriteria Penerimaan
- Black-box: 6 skenario Tabel 3.3 + validasi form (equivalence partitioning) + uji race condition dua konfirmasi bersamaan.
- Skenario tambahan: keranjang multi-item & total; manipulasi harga dari browser ditolak; konfirmasi tanpa DP ditolak; pembayaran melebihi sisa ditolak; DP → pelunasan mengubah status bayar; void pembayaran; invoice & kuitansi tercetak benar; grafik harian/bulanan berganti periode dengan benar.
- Responsif: ≥ 3 ukuran layar; uji interaksi folder di desktop (hover) dan ponsel (tap).
- SUS: ≥ 10 responden, rata-rata ≥ 70.
- Siap rilis: seluruh user story sprint 1–4 selesai, tanpa bug kritis, admin sudah menerima akun dan panduan.

## 13. Jadwal (dari Gantt proposal)
Proposal & desain: Okt minggu 3–4 → Prototipe interaktif & desain statis: Nov m2 → Sprint 1–4: Okt m4 – Nov m2 → Pengujian/SUS/deploy: Nov m2–m3 → Laporan: Nov m2–m3.

## 14. Risiko
| Risiko | Mitigasi |
|---|---|
| Aset portofolio/harga dari FZM terlambat | Gunakan data dummy; minta aset sejak sprint 1 |
| Hosting tidak cocok dengan Laravel | Putuskan hosting sebelum sprint 3 |
| Spam pada form publik | Rate limit, honeypot, captcha |
| Double booking karena race condition | Transaksi DB + unique index |
| Salah hitung total/DP | Hitung di server, nominal integer, uji otomatis untuk kalkulasi |
| Salah input pembayaran | Fitur void dengan alasan + audit (admin_id, waktu) |
| Animasi berat di ponsel | Hanya transform/opacity, gambar thumbnail, reduced-motion |
| Jadwal sprint padat (Sprint 2–4 paralel) | Prioritaskan: booking, pembayaran & admin dulu, promo/pencarian terakhir |

## 15. Keputusan yang Sudah Diambil
| Topik | Keputusan |
|---|---|
| Pending mengunci tanggal? | Tidak. Hanya `confirmed` yang mengunci. |
| Harga | Tetap per tingkat atau per meter (bukan "mulai dari"). |
| Model pemesanan | Keranjang multi-layanan, total otomatis. |
| DP | 50% dari total; wajib sebelum konfirmasi. |
| Pendapatan | Dihitung dari pembayaran yang dicatat admin. |
| Dokumen | Invoice per booking + kuitansi per pembayaran. |
| Jatuh tempo | Tanggal acara. |
| PPN | Nonaktif secara default; bisa diaktifkan admin (11%). |

## 16. Pertanyaan Terbuka
1. Hosting final?
2. Apakah satu hari boleh lebih dari satu event (kapasitas), atau 1 tanggal = 1 event?
3. Tingkat harga Lighting yang final (saat ini contoh 1,5jt / 2,25jt / 3jt).
4. Kebijakan DP jika acara dibatalkan (hangus / kembali sebagian)?
5. Data perusahaan untuk invoice: alamat, NPWP, rekening bank.
6. Bahasa: Indonesia saja?
